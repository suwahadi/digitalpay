<?php

namespace App\Providers\Filament;

use App\Helpers\WebpImage;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\View\PanelsRenderHook;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AdminPanelProvider extends PanelProvider
{
    /**
     * Every image uploaded from the admin panel, including rich editor attachments,
     * is validated by content and re-encoded to WebP under a server-generated name.
     * A future non-image FileUpload must override saveUploadedFileUsing() itself.
     */
    public function boot(): void
    {
        FileUpload::configureUsing(fn (FileUpload $upload) => $upload
            ->rule(static fn (): Closure => static function (string $attribute, mixed $value, Closure $fail): void {
                if ($value instanceof TemporaryUploadedFile && $problem = WebpImage::problem($value->getRealPath())) {
                    $fail($problem);
                }
            })
            ->saveUploadedFileUsing(static fn (FileUpload $component, TemporaryUploadedFile $file): string => WebpImage::store(
                $file,
                $component->getDiskName(),
                $component->getDirectory(),
                $component->getStatePath(),
            )));

        RichEditor::configureUsing(fn (RichEditor $editor) => $editor
            ->saveUploadedFileAttachmentsUsing(static fn (RichEditor $component, TemporaryUploadedFile $file): string => WebpImage::store(
                $file,
                $component->getFileAttachmentsDiskName(),
                $component->getFileAttachmentsDirectory(),
                $component->getStatePath(),
            )));
    }

    /**
     * Filament sizes the sidebar with h-screen (100vh). Mobile browsers measure
     * 100vh with the address bar hidden, so while it is showing the bottom of
     * the sidebar sits off screen and the last menu items cannot be scrolled
     * into view. 100dvh follows the visible viewport.
     */
    private const MOBILE_SIDEBAR_CSS = '<style>'
        .'@supports (height: 100dvh){.fi-sidebar{height:100dvh}}'
        .'.fi-sidebar-nav{overscroll-behavior:contain;padding-bottom:calc(2rem + env(safe-area-inset-bottom))}'
        .'</style>';

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): string => self::MOBILE_SIDEBAR_CSS)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
