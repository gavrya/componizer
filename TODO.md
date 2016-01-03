Important:

-- Update phpdocs

-- Update example plugin and widget

-- Rename ComponizerConfig to ComponizerConfig

-- Add required plugins to ContentProcessor

Unimportant:

-- Assets add array of parameters

-- Add Integration dir for Laravel and other frameworks integration

-- Add ComponizerMetaAsset ?

-- Add ComponizerTemplateAsset ?

-- ContentProcessor: add ability to pass array of content with target keys ($editorContent, $targetKeys = null).
   Usefull for parsing results from database rows with multiple "editor content" fields in each row.

-- Add ScopeManager/SettingsManager

-- How to return general componizer assets and symlink componizer assets?

-- How to link widgets with related plugin to group inside hwysiwyg editor?

-- Add type of preferred color theme: "dark" or "light"  ?

-- Create Laravel ServiceProvider ?

Code formatting (http://symfony.com/doc/current/contributing/code/standards.html):

-- Use just return; instead of return null; when a function must return void early;
-- Exception message strings should be concatenated using sprintf;
-- Prefix abstract classes with Abstract. Please note some early Symfony classes do not follow this convention and have not been renamed for backward compatibility reasons. However all new abstract classes must follow this naming convention;
-- Suffix interfaces with Interface;
-- Suffix traits with Trait;
-- Suffix exceptions with Exception;

Apigen:
site: http://www.apigen.org/
theme: https://github.com/ApiGen/ThemeBootstrap

php apigen.phar generate --template-theme="Twitter Bootstrap" --template-config="./apigen-theme-bootstrap/src/config.neon" -s src -d phpdoc

