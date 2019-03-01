<?php

namespace OlahTamas\VueCRUD\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VueCRUDGenerate extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vuecrud:generate {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates the necessary classes for a model to be handled with VueCRUD';


    protected function loadStub($name)
    {
        return __DIR__.'/stubs/'.$name.'.stub';
    }

    protected function getControllerName($name, $fullPath = false)
    {
        $basename = $name.'VueCRUDController';

        return $fullPath
            ? app_path('Http/Controllers/'.$basename.'.php')
            : $basename;
    }

    protected function getDataproviderName($name, $fullPath = false)
    {
        $basename = $name.'VueCRUDDataprovider';

        return $fullPath
            ? app_path('Dataproviders/'.$basename.'.php')
            : $basename;
    }

    protected function getFormdatabuilderName($name, $fullPath = false)
    {
        $basename = $name.'VueCRUDFormdatabuilder';

        return $fullPath
            ? app_path('Formdatabuilders/'.$basename.'.php')
            : $basename;
    }

    protected function getRequestName($name, $fullPath = false)
    {
        $basename = 'Save'.$name.'VueCRUDRequest';

        return $fullPath
            ? app_path('Http/Requests/'.$basename.'.php')
            : $basename;
    }

    protected function replaceSlug($stub, $slug)
    {
        return str_replace('DummySlug', $slug, $stub);
    }

    protected function buildController($name)
    {
        $stub = $this->files->get($this->loadStub('controller'));
        $slug = Str::slug($name);

        $stub = $this->replaceSlug($stub, $slug);
        $stub = $this->replaceClass($stub, $name);

        return $stub;
    }

    protected function buildRequest($name)
    {
        $stub = $this->files->get($this->loadStub('request'));

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function buildFormdatabuilder($name)
    {
        $stub = $this->files->get($this->loadStub('formdatabuilder'));

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function buildDataprovider($name)
    {
        $stub = $this->files->get($this->loadStub('dataprovider'));

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }


    protected function filesAlreadyExist($name)
    {
        return file_exists($this->getControllerName($name, true))
            || file_exists(app_path('/Http/Requests/'.$this->getRequestName($name).'.php'))
            || file_exists(app_path('/Dataproviders/'.$this->getDataproviderName($name).'.php'))
            || file_exists(app_path('/Formdatabuilders/'.$this->getFormdatabuilderName($name).'.php'));
    }


    /**
     * Execute the console command.
     *
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $name = $this->argument('model');

        if ((! $this->hasOption('force') ||
                ! $this->option('force')) &&
            $this->filesAlreadyExist($name)) {
            $this->error('Some of the files already exists!');

            return false;
        }

        $this->makeDirectory($this->getControllerName($name, true));
        $this->files->put($this->getControllerName($name, true), $this->buildController($name));
        $this->makeDirectory($this->getRequestName($name, true));
        $this->files->put($this->getRequestName($name, true), $this->buildRequest($name));
        $this->makeDirectory($this->getFormdatabuilderName($name, true));
        $this->files->put($this->getFormdatabuilderName($name, true), $this->buildFormdatabuilder($name));
        $this->makeDirectory($this->getDataproviderName($name, true));
        $this->files->put($this->getDataproviderName($name, true), $this->buildDataprovider($name));


        $this->info('VueCRUD scaffolding created successfully.');
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }
}
