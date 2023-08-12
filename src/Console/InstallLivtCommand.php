<?php

namespace Elijahcruz\Livt\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class InstallLivtCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'livt:install
        {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs just Inertia, Vue, Tailwind';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->callSilent('storage:link');

        if(file_exists(resource_path('views/welcome.blade.php'))) {
            unlink(resource_path('views/welcome.blade.php'));
        }

        if (! $this->requireComposerPackages('inertiajs/inertia-laravel:^0.6.8', 'tightenco/ziggy:^1.0')) {
            return false;
        }

        // Install NPM packages...
        $this->updateNodePackages(function ($packages) {
            return [
                    '@inertiajs/vue3' => '^1.0.0',
                    '@tailwindcss/forms' => '^0.5.2',
                    '@tailwindcss/typography' => '^0.5.2',
                    '@vitejs/plugin-vue' => '^4.0.0',
                    'autoprefixer' => '^10.4.7',
                    'postcss' => '^8.4.14',
                    'tailwindcss' => '^3.1.0',
                    'vue' => '^3.2.31',
                ] + $packages;
        });

        // Tailwind Configuration...
        copy(__DIR__.'/../../resources/stubs/install-livt/base/tailwind.config.js', base_path('tailwind.config.js'));
        copy(__DIR__.'/../../resources/stubs/install-livt/base/postcss.config.js', base_path('postcss.config.js'));
        copy(__DIR__.'/../../resources/stubs/install-livt/base/vite.config.js', base_path('vite.config.js'));

        // jsconfig.json...
        copy(__DIR__.'/../../resources/stubs/install-livt/base/jsconfig.json', base_path('jsconfig.json'));

        (new Filesystem)->ensureDirectoryExists(resource_path('css'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Components'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Layouts'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views'));

        (new Process([$this->phpBinary(), 'artisan', 'inertia:middleware', 'HandleInertiaRequests', '--force'], base_path()))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });

        copy(__DIR__.'/../../resources/stubs/install-livt/views/app.blade.php', resource_path('views/app.blade.php'));

        copy(__DIR__.'/../../resources/stubs/install-livt/js/Pages/Index.vue', resource_path('js/Pages/Index.vue'));

        copy(__DIR__.'/../../resources/stubs/install-livt/routes/web.php', base_path('routes/web.php'));

        copy(__DIR__.'/../../resources/stubs/install-livt/css/app.css', resource_path('css/app.css'));
        copy(__DIR__.'/../../resources/stubs/install-livt/js/app.js', resource_path('js/app.js'));

        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }

        $this->line('');
        $this->info('Inertia scaffolding installed successfully.');
    }

    /**
     * Run the given commands.
     *
     * @param  array  $commands
     * @return void
     */
    protected function runCommands($commands)
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });
    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param  mixed  $packages
     * @return bool
     */
    protected function requireComposerPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = [$this->phpBinary(), $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        return ! (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    /**
     * Update the "package.json" file.
     *
     * @param  callable  $callback
     * @param  bool  $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    protected function phpBinary()
    {
        return (new PhpExecutableFinder())->find(false) ?: 'php';
    }
}
