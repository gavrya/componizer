Important:

-- Add base class ComponizerAsset. Positions: HEADER | BODY_TOP | BODY_BOTTOM. Methods: toHtml(), toJson(), toArray(). Asset type: EXT_JS, INT_JS ?

-- Separate ComponizerConfig class !!!

-- Add AssetsManager ?

-- Omit Singleton usage for Componizer !!!

Unimportant:

-- ComponizerAssets: add ability to push/remove assets on the fly !!!

-- Prevent infinite loop in ContentParser !!!

-- ContentProcessor: add ability to pass array of content with target keys ($editorContent, $targetKeys = null).
   Usefull for parsing results from database rows with multiple "editor content" fields in each row.

-- Add ScopeManager/SettingsManager

-- How to return general componizer assets and symlink componizer assets?

-- How to link widgets with related plugin to group inside hwysiwyg editor?

-- Add type of preferred color theme: "dark" or "light"  ?

-- Add ComponizerMeta asset class for key=value passing to components js: <meta name="key" content="value">?

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
command: php apigen.phar generate --template-theme="Twitter Bootstrap" --template-config="./apigen-theme-bootstrap/src/config.neon" -s src -d phpdoc

