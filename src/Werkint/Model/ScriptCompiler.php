<?php
namespace Werkint\Model;

class ScriptCompiler
{

    protected $dirSrc;
    protected $dirDest;
    protected $dirTemplates;

    public function __construct(
        $dirSrc, $dirDest, $dirTemplates
    ) {
        $this->dirSrc = $dirSrc;
        $this->dirDest = $dirDest;
        $this->dirTemplates = $dirTemplates;
    }

    public function process()
    {
        $dir_src = $this->dirSrc;
        $dir_dest = $this->dirDest;
        $list = file($dir_src . '/.packages');
        copy($dir_src . '/.packages', $dir_dest . '/.packages');

        // Архиватор
        $archiver = new Archiver();

        // Хеши файлов
        $hashTable = array();

        // Таблица для html-файла
        $tableList = array();

        $count = 0;
        foreach ($list as $package) {
            $package = trim($package);
            echo 'Compiling ' . $package . "\n";

            // Запись для html-файлы
            $tableRow = array(
                'class'      => $package,
                'numScripts' => 0,
                'numRes'     => 0,
            );

            // Папка пакета-источника
            $pdir = $dir_src . '/' . $package;
            $pname = $pdir . '/.package.ini';
            $data = parse_ini_file($pname, true);
            // Папка пакета-приемника
            $tdir = $dir_dest . '/' . $package;
            $this->rmDir($tdir);
            mkdir($tdir);
            symlink($pname, $tdir . '/.package.ini');

            // Перечень файлов и зависимостей
            $lfiles = explode(',', $data['files']['files']);
            $tableRow['numScripts'] = count($lfiles);
            $lres = explode(',', $data['files']['res']);
            $tableRow['numRes'] = count($lres);
            //$deps = explode(',', $data['files']['deps']);

            // Хеши файлов
            $hashes = array();
            foreach (array_merge($lfiles, $lres) as $file) {
                if (!($file = trim($file))) {
                    continue;
                }
                if (is_dir($pdir . '/' . $file)) {
                    $archiver->compress(
                        $pdir . '/' . $file, $tdir . '/' . $file . '.zip'
                    );
                    $file .= '.zip';
                } else {
                    symlink($pdir . '/' . $file, $tdir . '/' . $file);
                }
                $hashes[] = sha1($file) . '=' . sha1(file_get_contents($tdir . '/' . $file));
            }
            $hashes = join("\n", $hashes) . "\n";
            file_put_contents($tdir . '/.hashes', $hashes);

            $hashTable[] = sha1($package) . '=' . sha1(
                sha1_file($tdir . '/.hashes') . sha1_file($tdir . '/.package.ini')
            );
            $count++;

            $tableList[] = $tableRow;
        }

        //$this->processView($tableList);

        file_put_contents($dir_dest . '/.hashes', join("\n", $hashTable) . "\n");
        return $count;
    }

    protected function rmDir($directory, $empty = false)
    {
        if (is_link($directory)) {
            unlink($directory);
            return true;
        }
        if (substr($directory, -1) == '/') {
            $directory = substr($directory, 0, -1);
        }
        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        } elseif (is_readable($directory)) {
            $handle = opendir($directory);
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    $path = $directory . '/' . $item;
                    if (is_dir($path)) {
                        $this->rmDir($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($handle);
            if ($empty == false) {
                if (!rmdir($directory)) {
                    return false;
                }
            }
        }
        return true;
    }
}