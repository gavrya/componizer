Important:

-- Add WidgetManager

-- Implement ContentProcessor with assets section working

-- Get unique components/widgets id's by content or array of content

Unimportant:

-- Check resolve instance for better ide integrity.

-- Improve DomHelper using <_root> element. This will reduce memory usage.

-- Prevent infinite loop in ContentParser

-- Move DomHelper to the ContentParser?

-- Add ScopeManager/SettingsManager

-- How to return general componizer assets and symlink componizer assets?

-- How to link widgets with related plugin to group inside hwysiwyg editor?

-- Add type of preferred color theme: "dark" or "light"  ?

-- Add ComponizerMeta asset class for key=value passing to components js: <meta name="key" content="value">?

-- Rename ContentProcessor to ContentManager?

Apigen:
site: http://www.apigen.org/
theme: https://github.com/ApiGen/ThemeBootstrap
command: php apigen.phar generate --template-theme="Twitter Bootstrap" --template-config="./apigen-theme-bootstrap/src/config.neon" -s src -d phpdoc

