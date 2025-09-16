<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Schemas;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Domain\Schemas\Schema;
use Setka\Cms\Domain\Workspaces\Workspace;

final class SchemaTest extends TestCase
{
    public function testGroupConfigurationAndVisibility(): void
    {
        $workspace = new Workspace('default', 'Default', ['en-US'], [], 1, bin2hex(random_bytes(16)));
        $collection = new Collection(
            workspace: $workspace,
            handle: 'articles',
            name: 'Articles',
            structure: CollectionStructure::FLAT,
            defaultSchemaId: null,
            urlRules: [],
            publicationRules: [],
            id: 10,
            uid: bin2hex(random_bytes(16)),
        );

        $title = new Field('title', 'Title', FieldType::TEXT, true);
        $type = new Field('type', 'Type', FieldType::TEXT, false);
        $summary = new Field('summary', 'Summary', FieldType::TEXT, false);

        $schema = new Schema($collection, 'article', 'Article Schema');
        $schema->addGroup('content', 'Content');
        $schema->addGroup('meta', 'Meta', condition: [
            'logic' => 'all',
            'rules' => [
                ['field' => 'type', 'operator' => 'equals', 'value' => 'article'],
            ],
        ]);

        $schema->addField($title, 'content', ['width' => 'full']);
        $schema->addField($type, 'content');
        $schema->addField($summary, 'meta', condition: [
            'logic' => 'any',
            'rules' => [
                ['field' => 'title', 'operator' => 'present'],
                ['field' => 'summary', 'operator' => 'present'],
            ],
        ]);

        $this->assertSame('content', $schema->getGroupForField('title'));
        $this->assertTrue($schema->isGroupVisible('content', ['type' => 'article']));
        $this->assertFalse($schema->isGroupVisible('meta', ['type' => 'blog']));
        $this->assertTrue($schema->isGroupVisible('meta', ['type' => 'article']));
        $this->assertFalse($schema->isFieldVisible('summary', ['type' => 'article', 'title' => '']));
        $this->assertTrue($schema->isFieldVisible('summary', ['type' => 'article', 'title' => 'Intro']));

        $schema->moveField('type', 'meta', 0);
        $this->assertSame('meta', $schema->getGroupForField('type'));

        $definition = $schema->exportDefinition();
        $this->assertSame('article', $definition['handle']);
        $this->assertCount(2, $definition['groups']);
        $this->assertSame('content', $definition['groups'][0]['handle']);
        $this->assertSame('meta', $definition['groups'][1]['handle']);
        $this->assertSame('title', $definition['groups'][0]['fields'][0]['handle']);
        $this->assertSame('type', $definition['groups'][1]['fields'][0]['handle']);
    }
}
