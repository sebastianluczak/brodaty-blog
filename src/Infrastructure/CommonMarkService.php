<?php

namespace App\Infrastructure;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;
use League\CommonMark\Extension\Embed\Bridge\OscaroteroEmbedAdapter;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Block\Paragraph;
use Spatie\CommonMarkShikiHighlighter\HighlightCodeExtension;

class CommonMarkService
{
    public function init(): MarkdownConverter
    {
        $config = $this->getConfig();
        $environment = $this->getEnvironment($config);

        return new MarkdownConverter($environment);
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function getEnvironment(array $config): Environment
    {
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new AttributesExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new DefaultAttributesExtension());
        $environment->addExtension(new DescriptionListExtension());
        $environment->addExtension(new EmbedExtension());
        $environment->addExtension(new FootnoteExtension());
        $environment->addExtension(new FrontMatterExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TableOfContentsExtension());
        $environment->addExtension(new HighlightCodeExtension('slack-dark'));

        return $environment;
    }

    /**
     * @return array<string, mixed> $config
     */
    protected function getConfig(): array
    {
        // Define your configuration, if needed
        return [
            'commonmark' => [
                'enable_em' => true,
                'enable_strong' => true,
                'use_asterisk' => true,
                'use_underscore' => true,
                'unordered_list_markers' => ['-', '+', '='],
            ],
            'default_attributes' => [
                Heading::class => [
                    'class' => static function (Heading $node) {
                        if (1 === $node->getLevel()) {
                            return 'title-main';
                        } else {
                            return null;
                        }
                    },
                ],
                BlockQuote::class => [
                    'class' => 'blockquote',
                ],
                Table::class => [
                    'class' => 'table table-responsive',
                ],
                Paragraph::class => [
                    'class' => ['text-grey', ''],
                ],
                Link::class => [
                    'class' => 'btn btn-xs btn-link text-black external-link',
                    'target' => '_blank',
                ],
            ],
            'table' => [
                'wrap' => [
                    'enabled' => false,
                    'tag' => 'div',
                    'attributes' => ['class' => 'table'],
                ],
            ],
            'embed' => [
                'adapter' => new OscaroteroEmbedAdapter(), // See the "Adapter" documentation below
                'allowed_domains' => ['youtube.com', 'twitter.com', 'github.com'],
                'fallback' => 'link',
            ],
            'footnote' => [
                'backref_class' => 'footnote-backref',
                'backref_symbol' => '↩',
                'container_add_hr' => true,
                'container_class' => 'footnotes',
                'ref_class' => 'footnote-ref',
                'ref_id_prefix' => 'fnref:',
                'footnote_class' => 'footnote',
                'footnote_id_prefix' => 'fn:',
            ],
            'heading_permalink' => [
                'html_class' => 'permalink',
                'id_prefix' => 'content',
                'fragment_prefix' => 'content',
                'insert' => 'before',
                'min_heading_level' => 1,
                'max_heading_level' => 6,
                'title' => 'Permalink',
                'symbol' => '🦄 ',
                'aria_hidden' => true,
            ],
            'table_of_contents' => [
                'html_class' => 'table-of-contents',
                'position' => 'top',
                'style' => 'bullet',
                'min_heading_level' => 1,
                'max_heading_level' => 6,
                'normalize' => 'relative',
                'placeholder' => null,
            ],
        ];
    }
}
