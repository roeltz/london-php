<?php

namespace London\Config;

Config::registerSource(new Source\PHPSource());
Config::registerSource(new Source\JSONSource());