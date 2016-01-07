Important:

-- Update phpdocs

-- Add getRequiredPlugins() to ContentProcessor

-- Change namespaces (Asset ->Asses, Component -> Components)

-- Add array of attributes to the Assets implementation classes

-- Add helper methods to the laravel componizer facade. ComponizerEditor::getHeadAssetsHtml();

-- Separate enablePlugin and enablePlugins

-- Rename Componizer to ComponizerEditor, laravel facade ComponizerEditor to Componizer

-- Parsing not working with two sibling element came from makeDisplayContent(): <div>content</div><p>content</p> -> <div>content</div> vs <div>content</div><p>content</p>

Unimportant:

-- Add ComponizerMetaAsset ?
-- Add ComponizerTemplateAsset ?

-- Add ScopeManager/SettingsManager

-- How to return general componizer assets and symlink componizer assets?

-- How to link widgets with related plugin to group inside hwysiwyg editor?

-- Add type of preferred color theme: "dark" or "light"  ?

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

