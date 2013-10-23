<?php

class RecursiveNamespaceExtractor
{
    private $_baseNamespaces;
    private $_namespaces;

    /**
     * @param $baseNamespaces array An associative array with the namespaces as the keys and the
     *        corresponding values as the paths
     */
    public function __construct($baseNamespaces)
    {
        $this->_baseNamespaces = $baseNamespaces;
    }

    /**
     * Returns the full list of namespaces (ready to be passed into $loader->registerNamespaces)
     */
    public function getNamespaces()
    {
        if (!is_null($this->_namespaces)) return $this->_namespaces;

        $this->_namespaces = $this->_baseNamespaces;

        foreach ($this->_baseNamespaces as $namespace => $path) {
            $dir = opendir($path);
            while (false !== ($file = readdir($dir))) {
                if (0 == strcmp('.', $file) || 0 == strcmp('..', $file)) continue;
                $this->_addAdditionalNamespaces($namespace, $path, $file);
            }
        }

        return $this->_namespaces;
    }

    // private helper function for recursive calls
    private function _addAdditionalNamespaces($parentNamespace, $parentPath, $newFile)
    {
        $fullPath = $parentPath.DIRECTORY_SEPARATOR.$newFile;
        if (!is_dir($fullPath)) return;

        $namespace = $parentNamespace.'\\'.$newFile;
        if (array_key_exists($namespace, $this->_namespaces)) return;

        $this->_namespaces[$namespace] = $fullPath;
        $dir = opendir($fullPath);
        while (false !== ($file = readdir($dir))) {
            if (0 == strcmp('.', $file) || 0 == strcmp('..', $file)) continue;
            $this->_addAdditionalNamespaces($namespace, $fullPath, $file);
        }
    }
}