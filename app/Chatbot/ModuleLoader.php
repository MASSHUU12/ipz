<?php

namespace App\Chatbot;

use Exception;

/**
 * Scans App/Chatbot/Modules for module classes that implement ModuleInterface.
 * Loads them and returns combined patterns and the highest mtime observed.
 */
class ModuleLoader
{
    /**
     * Discover and load modules. Returns ['patterns' => array, 'mtime' => int].
     *
     * @return array
     */
    public static function loadModules(): array
    {
        $modulesPath = app_path("Chatbot/Modules");
        $resultPatterns = [];
        $maxMtime = 0;

        if (!is_dir($modulesPath)) {
            return ["patterns" => [], "mtime" => 0];
        }

        $files = @scandir($modulesPath);
        if (!is_array($files)) {
            return ["patterns" => [], "mtime" => 0];
        }

        $declaredBefore = get_declared_classes();

        foreach ($files as $f) {
            if (substr($f, -4) !== ".php") {
                continue;
            }
            $filePath = $modulesPath . DIRECTORY_SEPARATOR . $f;
            try {
                require_once $filePath;
            } catch (Exception $e) {
                continue;
            }

            $mtime = @filemtime($filePath) ?: 0;
            $maxMtime = max($maxMtime, $mtime);
        }

        $declaredAfter = get_declared_classes();
        $new = array_diff($declaredAfter, $declaredBefore);
        foreach ($new as $class) {
            try {
                if (!in_array(ModuleInterface::class, class_implements($class) ?: [], true)) {
                    continue;
                }
                $patterns = $class::getPatterns();
                if (is_array($patterns)) {
                    foreach ($patterns as $p) {
                        if (!is_array($p)) {
                            continue;
                        }

                        // Normalize callback: if module returned a bare method name (string)
                        // and that method exists on the module class, bind it to [$class, $method].
                        if (
                            isset($p["callback"]) &&
                            is_string($p["callback"]) &&
                            strpos($p["callback"], "::") === false
                        ) {
                            $methodName = $p["callback"];
                            if (method_exists($class, $methodName)) {
                                $p["callback"] = [$class, $methodName];
                            }
                        }

                        $resultPatterns[] = $p;
                    }
                }
                $maxMtime = max($maxMtime, (int) ($class::getMTime() ?? 0));
            } catch (Exception $e) {
                continue;
            }
        }

        return ["patterns" => $resultPatterns, "mtime" => $maxMtime];
    }
}
