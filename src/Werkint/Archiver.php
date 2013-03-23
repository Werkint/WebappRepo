<?php
namespace Werkint;

use \ZipArchive;

class Archiver
{
    protected function addDirectoryToZip(ZipArchive $zip, $dir, $base)
    {
        $newFolder = str_replace($base, '', $dir);
        $zip->addEmptyDir($newFolder);
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $zip = $this->addDirectoryToZip($zip, $file, $base);
            } else {
                $newFile = str_replace($base, '', $file);
                $zip->addFile($file, $newFile);
            }
        }
        return $zip;
    }

    public function compress($path, $out)
    {
        $zip = new ZipArchive();
        $zip->open($out, ZipArchive::CREATE);
        $this->addDirectoryToZip(
            $zip, realpath($path), dirname(realpath($path))
        );
        $zip->close();
    }
}