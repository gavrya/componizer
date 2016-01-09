Important:

-- Add getRequiredPlugins() to ContentProcessor

Unimportant:

-- Add helper methods to the laravel componizer facade. ComponizerEditor::getHeadAssetsHtml();
-- Add ComponizerMetaAsset ?
-- Add ComponizerTemplateAsset ?
-- Add ScopeManager/SettingsManager
-- How to return general componizer assets and symlink componizer assets?
-- How to link widgets with related plugin to group inside hwysiwyg editor?


Seeds:

-- Add type of preferred color theme: "dark" or "light" ?


Apigen:
site: http://www.apigen.org/
theme: https://github.com/ApiGen/ThemeBootstrap

php apigen.phar generate --template-theme="Twitter Bootstrap" --template-config="./apigen-theme-bootstrap/src/config.neon" -s src -d phpdoc

Alternative api doc generator used in laravel:
Sami: an API documentation generator - https://github.com/FriendsOfPHP/Sami
