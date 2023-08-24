<?php

namespace Elijahcruz\Livt\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

class MakePageResourceCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:page-resource {name} {--only= : Comma separated list of pages to create} {--except= : Comma separated list of pages to not create}
        {--force : Overwrite the page if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a set of pages in the Pages directory for a resource (Eg. Client becomes Client/Index,Client/Create,Client/Show, etc.)';

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

        $stub = $this->getStub();

        if($this->option('only')){
            $pages = explode(',', $this->option('only'));
        }
        elseif($this->option(['except'])){
            $pages = ['Index', 'Create', 'Show', 'Edit'];
            $pages = array_diff($pages, explode(',', $this->option('except')));
        }
        else{
            $pages = ['Index', 'Create', 'Show', 'Edit'];
        }

        foreach ($pages as $page){
            $filesystem->put($directory.'/'.$page.'.vue', $stub);
        }

        $this->info('Pages created successfully.');
    }

    private function getStub()
    {
        $stubDirectory = __DIR__.'/../../resources/stubs/make-page/';

        $file = 'Page.vue';

        // Let's get the file contents
        return file_get_contents($stubDirectory.$file);

    }
}