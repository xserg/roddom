<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Jobs\SyncSubscriptionItemsJob;
use App\Models\Subscription;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $shouldDispatch = false;
        /**
         * @var Subscription $record
         */
        //обновляем subscription items только если обновили тип либо айди подписки
        //если например просто прикрепили пару лекций в админке - то не синкаем/обновляем subscription items
        //т.к. синк уберет все старые и поставит заново
        if ($record->subscriptionable_type !== $data['subscriptionable_type']
            || $record->subscriptionable_id !== $data['subscriptionable_id']) {
            $shouldDispatch = true;
        }

        $record->update($data);

        if ($shouldDispatch) {
            dispatch_sync(new SyncSubscriptionItemsJob($record));
        }
        return $record;
    }
}
