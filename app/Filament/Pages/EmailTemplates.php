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
            'reminder_subject' => Setting::get('mail.reminder_subject', MailTemplate::DEFAULTS['mail.reminder_subject']),
            'reminder_body'    => Setting::get('mail.reminder_body', MailTemplate::DEFAULTS['mail.reminder_body']),
            'birthday_subject' => Setting::get('mail.birthday_subject', MailTemplate::DEFAULTS['mail.birthday_subject']),
            'birthday_body'    => Setting::get('mail.birthday_body', MailTemplate::DEFAULTS['mail.birthday_body']),
            'birthday_whatsapp' => Setting::get('mail.birthday_whatsapp', MailTemplate::DEFAULTS['mail.birthday_whatsapp']),
            'birthday_auto'     => filter_var(Setting::get('mail.birthday_auto', '0'), FILTER_VALIDATE_BOOLEAN),
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

                Forms\Components\Section::make('Reminder email')
                    ->description('Used by the Send Reminder page — sent on demand to all students (or one centre).')
                    ->schema([
                        Forms\Components\TextInput::make('reminder_subject')->label('Subject')->required(),
                        ContentEditor::make('reminder_body')->label('Body'),
                    ]),

                Forms\Components\Section::make('Birthday greeting')
                    ->description('Email sent to students on their birthday; the WhatsApp text opens pre-filled when you click the WhatsApp button on the Students list.')
                    ->schema([
                        Forms\Components\TextInput::make('birthday_subject')->label('Email subject')->required(),
                        ContentEditor::make('birthday_body')->label('Email body'),
                        Forms\Components\Textarea::make('birthday_whatsapp')
                            ->label('WhatsApp message (plain text)')
                            ->rows(3)
                            ->helperText('Same placeholders work here, e.g. {student_name}, {centre}.'),
                        Forms\Components\Toggle::make('birthday_auto')
                            ->label('Send birthday emails automatically every morning')
                            ->helperText('When on, students whose birthday is today receive the email at 8:00 AM without any manual step.'),
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
            'mail.reminder_subject' => ['reminder_subject', 'text'],
            'mail.reminder_body'    => ['reminder_body', 'html'],
            'mail.birthday_subject' => ['birthday_subject', 'text'],
            'mail.birthday_body'    => ['birthday_body', 'html'],
            'mail.birthday_whatsapp' => ['birthday_whatsapp', 'text'],
        ];

        foreach ($map as $key => [$field, $type]) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $data[$field] ?? '', 'type' => $type, 'group' => 'mail'],
            );
        }

        Setting::updateOrCreate(
            ['key' => 'mail.birthday_auto'],
            ['value' => ($data['birthday_auto'] ?? false) ? '1' : '0', 'type' => 'boolean', 'group' => 'mail'],
        );

        Notification::make()->title('Email templates saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')->label('Save changes')->submit('save'),
        ];
    }
}
