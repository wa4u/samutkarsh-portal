<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\ContentEditor;
use App\Models\Setting;
use App\Support\MailTemplate;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

/**
 * Edit the registration email templates (student confirmation + HO/centre
 * notification). Stored in Settings (group "mail"); tokens like {student_name}
 * are filled in when the mail is sent.
 */
class EmailTemplates extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Email Templates';

    protected static string $view = 'filament.pages.email-templates';

    /** @var array<string,mixed> */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isTrustAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            'student_subject' => Setting::get('mail.student_subject', MailTemplate::DEFAULTS['mail.student_subject']),
            'student_body'    => Setting::get('mail.student_body', MailTemplate::DEFAULTS['mail.student_body']),
            'admin_subject'   => Setting::get('mail.admin_subject', MailTemplate::DEFAULTS['mail.admin_subject']),
            'admin_body'      => Setting::get('mail.admin_body', MailTemplate::DEFAULTS['mail.admin_body']),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('placeholders')
                    ->label('Available placeholders')
                    ->content(new HtmlString(
                        '<div style="display:flex;flex-wrap:wrap;gap:6px">'
                        . collect(MailTemplate::PLACEHOLDERS)->map(fn ($p) =>
                            '<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px">' . e($p) . '</code>')->implode('')
                        . '</div><p style="margin-top:6px;color:#64748b">Type these into the subject or body — they are replaced with the real values when the email is sent.</p>'
                    )),

                Forms\Components\Section::make('Student confirmation email')
                    ->description('Sent to the applicant after they register (only if they gave an email).')
                    ->schema([
                        Forms\Components\TextInput::make('student_subject')->label('Subject')->required(),
                        ContentEditor::make('student_body')->label('Body'),
                    ]),

                Forms\Components\Section::make('Head Office & centre notification')
                    ->description('Sent to Head Office and the relevant centre on every new registration.')
                    ->schema([
                        Forms\Components\TextInput::make('admin_subject')->label('Subject')->required(),
                        ContentEditor::make('admin_body')->label('Body'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $map = [
            'mail.student_subject' => ['student_subject', 'text'],
            'mail.student_body'    => ['student_body', 'html'],
            'mail.admin_subject'   => ['admin_subject', 'text'],
            'mail.admin_body'      => ['admin_body', 'html'],
        ];

        foreach ($map as $key => [$field, $type]) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $data[$field] ?? '', 'type' => $type, 'group' => 'mail'],
            );
        }

        Notification::make()->title('Email templates saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')->label('Save changes')->submit('save'),
        ];
    }
}
