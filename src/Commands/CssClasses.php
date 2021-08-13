<?php

namespace Datalytix\VueCRUD\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CssClasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vuecrud:cssclasses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds all getCSSClass calls and extracts the class names';

    protected $pattern = '/getCSSClass\((.*?)\)/miu';

    public function scandirRecursive($path, $filterString = null)
    {
        $list = array_diff(scandir($path), array( ".", ".." ));
        if ($filterString !== null) {
            $newList = [];
            foreach ($list as $index => $listItem) {
                if ((stripos($listItem, $filterString) !== false) || (is_dir($path.DIRECTORY_SEPARATOR.$listItem))) {
                    $newList[] = $listItem;
                }
            }
            $list = $newList;
        }
        $list2 = array();
        foreach ($list as $l) {
            if (is_dir($path.DIRECTORY_SEPARATOR.$l)) {
                $list3 = array_diff($this->scandirRecursive($path.DIRECTORY_SEPARATOR.$l), array( ".", ".." ));
                foreach ($list3 as $l3) {
                    if (($filterString == null)
                        || (($filterString != null) && (stripos($l3, $filterString) !== false))) {
                        $list2[] = $l.DIRECTORY_SEPARATOR.$l3;
                    }
                }
            }
        }
        return array_merge($list, $list2);
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $path = base_path();
        $allfiles = [
            $path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js' => $this->scandirRecursive($path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'),
            $path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views' => $this->scandirRecursive($path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views', '.php')
        ];
        $unfilteredResults = [];
        foreach ($allfiles as $basepath => $files) {
            foreach ($files as $file) {
                $content = '';
                try {
                    $content = file_get_contents($basepath.DIRECTORY_SEPARATOR.$file);
                } catch (\Exception $e) {
                }
                $matches = [];
                preg_match_all($this->pattern, $content, $matches);
                if (count($matches[0]) > 0) {
                    $unfilteredResults = array_merge($unfilteredResults, $matches[1]);
                }
            }
        }
        $results = collect($unfilteredResults)->filter(function($item) {
            return !array_key_exists(str_ireplace('\'', '', $item), config('vuecrud.cssClasses', []));
        })->transform(function($item) {
            $result = str_ireplace('\'', '"', $item);
            $pieces = explode('", "', $result);
            if (count($pieces) > 1) {
                $result = $pieces[0].'"';
            }
            return $result.' => "",';
        });
        $filename = storage_path('app'.DIRECTORY_SEPARATOR.'vuecrudcssclasses-'.now()->format('Y-m-d-H-i-s').'.txt');
        file_put_contents($filename, $results->implode("\n"));
        $this->info('List generated: '.$filename);
    }

}
