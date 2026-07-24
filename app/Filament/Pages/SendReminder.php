<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\ContentEditor;
use App\Models\Center;
use App\Models\Setting;
use App\Models\Student;
use App\Mail\TemplatedStudentMail;
use App\Support\MailTemplate;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

/**
 * Send a one-off reminder email to every student with an email on file —
 * all centres or a single centre. Subject/body are prefilled from the
 * admin-editable Reminder template and can be tweaked before sending.
 */
class SendReminder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Send Reminder';

    protected static string $view = 'filament.pages.send-reminder';

    /** @var array<string,mixed> */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isTrustAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            'center_id' => null,
            'subject'   => Setting::get('mail.reminder_subject', MailTemplate::DEFAULTS['mail.reminder_subject']),
            'body'      => Setting::get('mail.reminder_body', MailTemplate::DEFAULTS['mail.reminder_body']),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('center_id')
                    ->label('Centre')
                    ->placeholder('All centres')
                    ->options(Center::orderBy('name')->pluck('name', 'id'))
                    ->live()
                    ->helperText(fn (Forms\Get $get) => 'Will be sent to '
                        . Student::query()
                            ->whereNotNull('email')->where('email', '!=', '')
                            ->when($get('center_id'), fn ($q, $id) => $q->where('center_id', $id))
                            ->count()
                        . ' student(s) with an email on file.'),

                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->helperText('Placeholders like {student_name} and {centre} are replaced per student.'),

                ContentEditor::make('body')->label('Body'),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $sent = 0;
        Student::query()
            ->whereNotNull('email')->where('email', '!=', '')
            ->when($data['center_id'] ?? null, fn ($q, $id) => $q->where('center_id', $id))
            ->with('center')
            ->chunkById(100, function ($students) use ($data, &$sent) {
                foreach ($students as $student) {
                    $tokens = MailTemplate::studentTokens($student);
                    Mail::to($student->email)->send(new TemplatedStudentMail(
                        trim(strip_tags(strtr($data['subject'], $tokens))),
                        strtr($data['body'] ?? '', $tokens),
                    ));
                    $sent++;
                }
            });

        Notification::make()
            ->title("Reminder sent to {$sent} student(s)")
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('send')->label('Send reminder')->submit('send'),
        ];
    }
}
