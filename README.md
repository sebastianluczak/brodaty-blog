# brodaty-blog

Simple Blog Engine based on pure Markdown files. Works without database, caches HTML templates from Markdown files. Fast and extendable.

![image](https://user-images.githubusercontent.com/1628839/190449430-e0601861-68fe-40e3-a1e1-572b63a5032e.png)

## Live Site

Live example: https://blog.brodaty.dev

## Local development

```shell
composer install
npm install
symfony serve
```

## Usage

Create directory for your Markdown documents.

```shell
mkdir -p resources/articles
```

Upload any number of documents in given directory and clear cache.

```shell
php bin/console app:clear
```

Your Markdown files will be cached and served as HTML files in under 10ms!

## Customization

You can freely customize `Presentation` layer in `src/Presentation` to your liking.