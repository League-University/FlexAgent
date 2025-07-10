<?php

namespace 'FlexAgent';

class Continuity {
  public static function push ($env = null) {
    foreach (FlexAgent::getEnvironments($env) as $env) {
      try {
        if (FlexAgent::SETTINGS["sync_$env"] && \FlexAgent::$agent[$env]) {
          $agent    = \FlexAgent::$agent;
          $datetime = date(\FlexAgent::SETTINGS['datetime_format']);
          \FlexAgent\Actions::act('bash', <<<BASH
cd $agent[$env.'_dir']
git commit -am "Snapshot taken at [$datetime]"
git push -u $agent['branch']
BASH
          );
        }
      } catch (Throwable $e) {
        \FlexAgent::addOutput(Markdown::makeBlock($e->__toString(), 'stderr'));
      }
    }
  }

  public static function pull ($env = null) {
    foreach (FlexAgent::getEnvironments($env) as $env) {
      try {
        if (FlexAgent::SETTINGS["sync_$env"] && \FlexAgent::$agent[$env]) {
          $agent    = \FlexAgent::$agent;
          $datetime = date(\FlexAgent::SETTINGS['datetime_format']);
          \FlexAgent\Actions::act('bash', <<<BASH
cd $agent[$env.'_dir']
git fetch origin
git reset --hard origin/$agent['branch']
BASH
          );
        }
      } catch (Throwable $e) {
        \FlexAgent::addOutput(Markdown::makeBlock($e->__toString(), 'stderr'));
      }
    }
  }
}
