<?php

namespace Elijahcruz\Livt\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

class MakePageCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:page {name}
        {--C|component : Make a component instead of a page}
        {--force : Overwrite the page if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new Inertia page in the Pages directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        if($this->option('component')){
            $pageDirectory = resource_path('js/Components/');
        }
        else{
            $pageDirectory = resource_path('js/Pages/');
        }

        // Check if the page is in dot notation
        if (strpos($name, '.') !== false) {
            $name = str_replace('.', '/', $name);
        }

        $array = explode('/', $name);

        if(!$this->option('force')){
            // Lets check if the page already exists
            if (File::exists($pageDirectory.implode('/', $array).'.vue')) {
                $this->error('Page already exists!');

                return;
            }
        }

        // Pop the page name off the end of the array
        $pageName = ucfirst(array_pop($array));

        $totalName = $pageName;

        $filesystem = new Filesystem();

        if (! empty($array)) {
            $array = array_map(function ($item) {
                // If the first letter in the string is not a capital letter, make it one
                // If it is a capital letter, leave it alone
                if (ctype_upper($item[0])) {
                    return $item;
                } else {
                    return ucfirst($item);
                }
            }, $array);

            // Put the array back together without the page name;
            $directory = $pageDirectory.implode('/', $array);

            $filesystem->ensureDirectoryExists($directory);
        }
        else {
            $directory = $pageDirectory;
        }

        $stub = $this->getStub();

        $filesystem->put($directory.'/'.$pageName.'.vue', $stub);

        $this->info('Page created successfully.');
    }

    private function getStub()
    {
        $stubDirectory = __DIR__.'/../../resources/stubs/make-page/';

        $file = 'Page.vue';

        // Let's get the file contents
        return file_get_contents($stubDirectory.$file);

    }
}
