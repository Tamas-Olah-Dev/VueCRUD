<?php


namespace Datalytix\VueCRUD\Traits;


trait VueCRUDFileModel
{
    public $fileinfo;

    public static function readPath($path, $sortFilesBy = 'name', $sortingDirection = 'asc', $haveDirectoriesFirst = true)
    {
        $iterator = new \DirectoryIterator($path);
        $records = static::where(static::PATH_PROPERTY, '=', $path)
            ->get()
            ->keyBy(static::NAME_PROPERTY);
        $dirs = [];
        $files = [];
        while($iterator->valid()) {
            if (!$iterator->current()->isDot()) {
                $file = $records->get($iterator->current()->getFilename());
                if ($file == null) {
                    $file = new static();
                    $file->id = $iterator->current()->getPath().DIRECTORY_SEPARATOR.$iterator->current()->getFilename();
                    $file->{static::NAME_PROPERTY} = $iterator->current()->getFilename();
                    $file->{static::PATH_PROPERTY} = $iterator->current()->getPath();
                }
                $file->fileinfo = static::fileinfoToArray(clone $iterator->current());
                if ($iterator->current()->isDir()) {
                    $dirs[] = $file;
                } else {
                    $files[] = $file;
                }

            }
            $iterator->next();
        }
        if ($haveDirectoriesFirst) {
            return array_merge(
                static::sortFileList($dirs, $sortFilesBy, $sortingDirection),
                static::sortFileList($files, $sortFilesBy, $sortingDirection)
            );
        }
        return static::sortFileList(array_merge($dirs, $files), $sortFilesBy, $sortingDirection);
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + ['fileinfo' => $this->fileinfo];
    }

    public static function fileinfoToArray($fileinfo)
    {
        return [
            'extension' => $fileinfo->getExtension(),
            'aTime' => $fileinfo->getaTime(),
            'mTime' => $fileinfo->getmTime(),
            'cTime' => $fileinfo->getcTime(),
            'size' => $fileinfo->getSize(),
            'type' => $fileinfo->getType(),
            'file' => $fileinfo->isFile(),
            'dir' => $fileinfo->isDir(),
            'link' => $fileinfo->isLink(),
        ];
    }

    protected static function sortFileList($list, $sortBy, $direction = 'asc')
    {
        $result = $list;
        $directionMultiplier = $direction == 'asc' ? 1 : -1;
        usort($result, function($a, $b) use ($sortBy, $directionMultiplier) {
            $values = [$a->{$sortBy}, $b->{$sortBy}];
            if (array_key_exists($sortBy, $a->fileinfo)) {
                $values = [$a->fileinfo[$sortBy], $b->fileinfo[$sortBy]];
            }
            if ($values[0] == $values[1]) {
                return 0;
            }
            return $values[0] < $values[1] ? -1 * $directionMultiplier : 1 * $directionMultiplier;
        });

        return $result;
    }
}