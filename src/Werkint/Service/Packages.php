<?php
namespace Werkint\Service;

use Silex\Application,
    Werkint\Model\Package;

class Packages
{

    protected $app;
    protected $packdir;

    public function __construct(
        Application $app,
        $packdir
    ) {
        $this->app = $app;
        $this->packdir = $packdir;
    }

    public function findAll()
    {
        $query = 'SELECT * FROM package';
        $rows = $this->app['db']->query($query);
        $list = [];
        foreach ($rows as $row) {
            $list[] = $this->formPackage($row);
        }
        return $list;
    }

    public function findByClass($class)
    {
        $query = 'SELECT * FROM package WHERE class = ?';
        $row = $this->app['db']->fetchAssoc(
            $query, [$class]
        );
        if (!$row) {
            return null;
        }
        return $this->formPackage($row);
    }

    protected function formPackage(array $row)
    {
        $package = new Package($row['id']);
        $package
            ->setClass($row['class'])
            ->setTitle($row['title']);
        return $package;
    }

}