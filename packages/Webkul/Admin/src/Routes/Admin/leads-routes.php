<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Lead\ActivityController;
use Webkul\Admin\Http\Controllers\Lead\EmailController;
use Webkul\Admin\Http\Controllers\Lead\FacebookOAuthController;
use Webkul\Admin\Http\Controllers\Lead\LeadConnectorController;
use Webkul\Admin\Http\Controllers\Lead\LeadController;
use Webkul\Admin\Http\Controllers\Lead\QuoteController;
use Webkul\Admin\Http\Controllers\Lead\TagController;

Route::controller(LeadConnectorController::class)->prefix('settings/lead-connectors')->group(function () {
    Route::get('', 'index')->name('admin.settings.lead_connectors.index');
    Route::post('store', 'store')->name('admin.settings.lead_connectors.store');
    Route::put('update/{id}', 'update')->name('admin.settings.lead_connectors.update');
    Route::delete('{id}', 'destroy')->name('admin.settings.lead_connectors.delete');
    Route::post('test-mapping', 'testMapping')->name('admin.settings.lead_connectors.test_mapping');
    Route::get('check-duplicate', 'checkDuplicate')->name('admin.leads.check_duplicate');

    // Meta (Facebook/Instagram) OAuth "Connect" flow — admin-initiated, returns to admin.
    Route::get('{id}/facebook/connect', [FacebookOAuthController::class, 'connect'])->name('admin.settings.lead_connectors.facebook.connect');
    Route::get('facebook/callback', [FacebookOAuthController::class, 'callback'])->name('admin.settings.lead_connectors.facebook.callback');
});

// NOTE: the public webhook receivers and QR form are registered in the Admin
// package's Front/web.php (no admin auth), so external platforms can reach them.

Route::controller(LeadController::class)->prefix('leads')->group(function () {
    Route::get('', 'index')->name('admin.leads.index');

    Route::get('inbox', 'inbox')->name('admin.leads.inbox');

    Route::get('inbox/data', 'inboxData')->name('admin.leads.inbox.data');

    Route::post('inbox/swipe', 'swipeAction')->name('admin.leads.inbox.swipe');

    Route::post('inbox/bulk', 'bulkAction')->name('admin.leads.inbox.bulk');

    Route::get('create', 'create')->name('admin.leads.create');

    Route::post('create', 'store')->name('admin.leads.store');

    Route::post('create-by-ai', 'createByAI')->name('admin.leads.create_by_ai');

    Route::get('view/{id}', 'view')->name('admin.leads.view');

    Route::get('edit/{id}', 'edit')->name('admin.leads.edit');

    Route::put('edit/{id}', 'update')->name('admin.leads.update');

    Route::put('attributes/edit/{id}', 'updateAttributes')->name('admin.leads.attributes.update');

    Route::put('stage/edit/{id}', 'updateStage')->name('admin.leads.stage.update');

    Route::get('search', 'search')->name('admin.leads.search');

    Route::delete('{id}', 'destroy')->name('admin.leads.delete');

    Route::post('mass-update', 'massUpdate')->name('admin.leads.mass_update');

    Route::post('mass-destroy', 'massDestroy')->name('admin.leads.mass_delete');

    Route::get('duplicate/{id}', 'duplicate')->name('admin.leads.duplicate');

    Route::post('merge/{id}', 'mergeStore')->name('admin.leads.merge.store');

    Route::get('get/{pipeline_id?}', 'get')->name('admin.leads.get');

    Route::delete('product/{lead_id}', 'removeProduct')->name('admin.leads.product.remove');

    Route::put('product/{lead_id}', 'addProduct')->name('admin.leads.product.add');

    Route::get('kanban/look-up', [LeadController::class, 'kanbanLookup'])->name('admin.leads.kanban.look_up');

    Route::controller(ActivityController::class)->prefix('{id}/activities')->group(function () {
        Route::get('', 'index')->name('admin.leads.activities.index');
    });

    Route::controller(TagController::class)->prefix('{id}/tags')->group(function () {
        Route::post('', 'attach')->name('admin.leads.tags.attach');

        Route::delete('', 'detach')->name('admin.leads.tags.detach');
    });

    Route::controller(EmailController::class)->prefix('{id}/emails')->group(function () {
        Route::post('', 'store')->name('admin.leads.emails.store');

        Route::delete('', 'detach')->name('admin.leads.emails.detach');
    });

    Route::controller(QuoteController::class)->prefix('quotes')->group(function () {
        Route::post('{quote_id}/mail', 'mail')->name('admin.leads.quotes.mail');

        Route::delete('{quote_id?}', 'delete')->name('admin.leads.quotes.delete');
    });
});
