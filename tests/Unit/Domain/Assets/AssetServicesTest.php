<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Assets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Setka\Cms\Contracts\Assets\AssetStorageInterface;
use Setka\Cms\Contracts\Assets\ElementAssetRepositoryInterface;
use Setka\Cms\Domain\Assets\Asset;
use Setka\Cms\Domain\Assets\AssetFileService;
use Setka\Cms\Domain\Assets\AssetVariantService;
use Setka\Cms\Domain\Assets\ElementAsset;
use Setka\Cms\Domain\Assets\ElementAssetCollection;
use Setka\Cms\Domain\Assets\ElementAssetService;
use Setka\Cms\Domain\Workspaces\Workspace;

final class AssetServicesTest extends TestCase
{
    public function testUploadAndReadAsset(): void
    {
        $storage = new InMemoryAssetStorage();
        $fileService = new AssetFileService($storage);

        $workspace = new Workspace('default', 'Default', ['en-US'], globalSettings: [], id: 1);
        $asset = new Asset($workspace, 'example.png', 'image/png', id: 10);

        $contents = "binary-data";
        $fileService->store($asset, $contents);

        self::assertSame(strlen($contents), $asset->getSize());
        self::assertSame($contents, $fileService->read($asset));
    }

    public function testStoreVariantUpdatesAsset(): void
    {
        $storage = new InMemoryAssetStorage();
        $variantService = new AssetVariantService($storage);

        $workspace = new Workspace('default', 'Default', ['en-US'], globalSettings: [], id: 2);
        $asset = new Asset($workspace, 'hero.jpg', 'image/jpeg', size: 128_000, id: 11);

        $variant = $variantService->storeVariant(
            $asset,
            'thumb',
            'thumb-data',
            'image/jpeg',
            ['width' => 320, 'height' => 180]
        );

        self::assertTrue($asset->hasVariant('thumb'));
        self::assertSame(320, $variant->getWidth());
        self::assertSame('thumb-data', $variantService->readVariant($asset, 'thumb'));

        $variantService->deleteVariant($asset, 'thumb');
        self::assertFalse($asset->hasVariant('thumb'));
    }

    public function testAttachAndListAssets(): void
    {
        $repository = new InMemoryElementAssetRepository();
        $service = new ElementAssetService($repository);

        $workspace = new Workspace('default', 'Default', ['en-US'], globalSettings: [], id: 3);
        $heroAsset = new Asset($workspace, 'hero.jpg', 'image/jpeg', size: 256_000, id: 21);
        $galleryAsset = new Asset($workspace, 'gallery.jpg', 'image/jpeg', size: 512_000, id: 22);

        $first = $service->attach($workspace, $heroAsset, 42, 'gallery', ['thumb']);
        self::assertSame(0, $first->getPosition());
        self::assertSame(['thumb'], $first->getVariants());

        $second = $service->attach($workspace, $galleryAsset, 42, 'gallery');
        self::assertSame(1, $second->getPosition());

        $list = $service->list($workspace, 42, 'gallery');
        self::assertCount(2, $list);

        $items = iterator_to_array($list);
        self::assertSame($heroAsset->getId(), $items[0]->getAssetId());
        self::assertSame($galleryAsset->getId(), $items[1]->getAssetId());

        $service->reorder($workspace, 42, 'gallery', [$galleryAsset->getId(), $heroAsset->getId()]);
        $ordered = iterator_to_array($service->list($workspace, 42, 'gallery'));
        self::assertSame($galleryAsset->getId(), $ordered[0]->getAssetId());
        self::assertSame(0, $ordered[0]->getPosition());
        self::assertSame(1, $ordered[1]->getPosition());

        $service->detach($workspace, $galleryAsset, 42, 'gallery');
        $remaining = iterator_to_array($service->list($workspace, 42, 'gallery'));
        self::assertCount(1, $remaining);
        self::assertSame($heroAsset->getId(), $remaining[0]->getAssetId());
        self::assertSame(0, $remaining[0]->getPosition());
    }
}

/**
 * Простая in-memory реализация файлового хранилища для тестов.
 */
final class InMemoryAssetStorage implements AssetStorageInterface
{
    /** @var array<string, string> */
    private array $files = [];

    public function write(string $path, string $contents): void
    {
        $this->files[$path] = $contents;
    }

    public function read(string $path): string
    {
        if (!isset($this->files[$path])) {
            throw new RuntimeException('File not found: ' . $path);
        }

        return $this->files[$path];
    }

    public function delete(string $path): void
    {
        unset($this->files[$path]);
    }
}

/**
 * In-memory репозиторий привязок медиа к элементам для тестов.
 */
final class InMemoryElementAssetRepository implements ElementAssetRepositoryInterface
{
    /** @var array<int, ElementAsset> */
    private array $items = [];

    private int $autoIncrement = 1;

    public function findByElement(int $workspaceId, int $elementId, ?string $role = null): ElementAssetCollection
    {
        $matches = [];
        foreach ($this->items as $item) {
            if ($item->getWorkspaceId() !== $workspaceId) {
                continue;
            }

            if ($item->getElementId() !== $elementId) {
                continue;
            }

            if ($role !== null && $role !== '' && $item->getRole() !== $role) {
                continue;
            }

            $matches[] = clone $item;
        }

        return new ElementAssetCollection(...$matches);
    }

    public function findOne(int $workspaceId, int $elementId, int $assetId, string $role): ?ElementAsset
    {
        foreach ($this->items as $item) {
            if ($item->getWorkspaceId() !== $workspaceId) {
                continue;
            }

            if ($item->getElementId() !== $elementId) {
                continue;
            }

            if ($item->getAssetId() !== $assetId) {
                continue;
            }

            if ($item->getRole() !== $role) {
                continue;
            }

            return clone $item;
        }

        return null;
    }

    public function save(ElementAsset $attachment): void
    {
        if ($attachment->getId() === null) {
            $attachment->defineId($this->autoIncrement++);
        }

        $this->items[$attachment->getId()] = clone $attachment;
    }

    public function delete(ElementAsset $attachment): void
    {
        $id = $attachment->getId();
        if ($id !== null) {
            unset($this->items[$id]);

            return;
        }

        foreach ($this->items as $key => $item) {
            if (
                $item->getWorkspaceId() === $attachment->getWorkspaceId()
                && $item->getElementId() === $attachment->getElementId()
                && $item->getAssetId() === $attachment->getAssetId()
                && $item->getRole() === $attachment->getRole()
            ) {
                unset($this->items[$key]);
            }
        }
    }
}
