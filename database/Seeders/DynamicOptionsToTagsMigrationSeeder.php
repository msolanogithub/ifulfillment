<?php

namespace Modules\Ifulfillment\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Ifulfillment\Models\DynamicOptions;
use Modules\Ifulfillment\Models\Shipment;
use Modules\Ifulfillment\Models\ShipmentItem;
use Modules\Itag\Models\Tag;
use Modules\Itag\Models\Taggable;

class DynamicOptionsToTagsMigrationSeeder extends Seeder
{
  public Carbon $now;
  public Collection $tags;
  public array $taggables = [];

  public function run(): void
  {
    if (!Tag::query()->exists()) {
      $this->now = now();
      $this->migrateLegacyTags();
      $this->tags = Tag::query()->get()
        ->keyBy(fn($tag) => "{$tag->type}|{$tag->value}");
      $this->migrateShipment();
      $this->migrateShipmentItem();
      $this->flushTaggables();
    }
  }

  public function migrateLegacyTags(): void
  {
    $legacyTags = DynamicOptions::query()
      ->get()
      ->makeHidden(['id'])
      ->toArray();

    Tag::query()->insert($legacyTags);
  }

  public function addTaggable(array $data): void
  {
    $this->taggables[] = [
      ...$data,
      'created_at' => $this->now,
      'updated_at' => $this->now,
    ];
    if (count($this->taggables) >= 500) $this->flushTaggables();
  }

  private function flushTaggables(): void
  {
    if (empty($this->taggables)) return;
    Taggable::query()->insertOrIgnore($this->taggables);
    $this->taggables = [];
  }

  public function migrateShipment(): void
  {
    Shipment::query()->chunkById(500, function ($shipments) {
      foreach ($shipments as $shipment) {
        $this->migrateCarrier($shipment);
        $this->migrateTags($shipment);
      }
    });
  }

  private function migrateCarrier($shipment): void
  {
    $carrier = data_get($shipment->options, 'carrier');
    //Carrier
    if ($carrier) {
      $carrierTag = $this->tags->get("carrier|{$carrier}");
      if ($carrierTag) {
        $this->addTaggable([
          'tag_id' => $carrierTag->id,
          'taggable_id' => $shipment->id,
          'taggable_type' => Shipment::class
        ]);
      }
    }
  }

  private function migrateTags($shipment): void
  {
    $tags = data_get($shipment->options, 'tags', []);
    foreach ($tags as $tagName) {
      $tag = $this->tags->get("shipmentTags|{$tagName}");
      if ($tag) {
        $this->addTaggable([
          'tag_id' => $tag->id,
          'taggable_id' => $shipment->id,
          'taggable_type' => Shipment::class
        ]);
      }
    }
  }

  public function migrateShipmentItem(): void
  {
    ShipmentItem::query()
      ->chunkById(500, function ($items) {
        foreach ($items as $item) {
          $packer = data_get($item->options, 'packer');
          if (!$packer) continue;

          $packerTag = $this->tags->get("packer|{$packer}");
          if ($packerTag) {
            $this->addTaggable([
              'tag_id' => $packerTag->id,
              'taggable_id' => $item->id,
              'taggable_type' => ShipmentItem::class,
            ]);
          }
        }
      });
  }
}
