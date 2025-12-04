<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VcardSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        // Name
        'first_name',
        'last_name',
        'nickname',
        'prefix',
        'suffix',
        // Organization
        'organization',
        'job_title',
        'department',
        // Contact
        'email',
        'email_work',
        'phone',
        'phone_work',
        'phone_mobile',
        'fax',
        // Address
        'address_street',
        'address_city',
        'address_state',
        'address_zip',
        'address_country',
        // Online
        'website',
        // Additional
        'birthday',
        'notes',
        // Settings
        'is_active',
        'include_photo',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'is_active' => 'boolean',
            'include_photo' => 'boolean',
        ];
    }

    /**
     * Get the profile this vCard belongs to.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->prefix,
            $this->first_name,
            $this->last_name,
            $this->suffix,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get the full address.
     */
    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address_street,
            $this->address_city,
            $this->address_state,
            $this->address_zip,
            $this->address_country,
        ]);

        return count($parts) > 0 ? implode(', ', $parts) : null;
    }

    /**
     * Generate vCard content (VCF format).
     */
    public function generateVcard(): string
    {
        $vcard = "BEGIN:VCARD\r\n";
        $vcard .= "VERSION:3.0\r\n";

        // Name
        $vcard .= "N:{$this->last_name};{$this->first_name};{$this->nickname};{$this->prefix};{$this->suffix}\r\n";
        $vcard .= "FN:{$this->full_name}\r\n";

        if ($this->nickname) {
            $vcard .= "NICKNAME:{$this->nickname}\r\n";
        }

        // Organization
        if ($this->organization) {
            $vcard .= "ORG:{$this->organization}\r\n";
        }

        if ($this->job_title) {
            $vcard .= "TITLE:{$this->job_title}\r\n";
        }

        // Contact methods
        if ($this->email) {
            $vcard .= "EMAIL;TYPE=HOME:{$this->email}\r\n";
        }

        if ($this->email_work) {
            $vcard .= "EMAIL;TYPE=WORK:{$this->email_work}\r\n";
        }

        if ($this->phone) {
            $vcard .= "TEL;TYPE=HOME:{$this->phone}\r\n";
        }

        if ($this->phone_work) {
            $vcard .= "TEL;TYPE=WORK:{$this->phone_work}\r\n";
        }

        if ($this->phone_mobile) {
            $vcard .= "TEL;TYPE=CELL:{$this->phone_mobile}\r\n";
        }

        if ($this->fax) {
            $vcard .= "TEL;TYPE=FAX:{$this->fax}\r\n";
        }

        // Address
        if ($this->address_street || $this->address_city) {
            $vcard .= "ADR;TYPE=WORK:;;{$this->address_street};{$this->address_city};{$this->address_state};{$this->address_zip};{$this->address_country}\r\n";
        }

        // Website
        if ($this->website) {
            $vcard .= "URL:{$this->website}\r\n";
        }

        // Birthday
        if ($this->birthday) {
            $vcard .= "BDAY:{$this->birthday->format('Ymd')}\r\n";
        }

        // Notes
        if ($this->notes) {
            $vcard .= "NOTE:" . str_replace(["\r", "\n"], '\n', $this->notes) . "\r\n";
        }

        // Photo (if enabled and profile has photo)
        if ($this->include_photo && $this->profile->photo) {
            // Photo would be base64 encoded in production
            // $vcard .= "PHOTO;ENCODING=BASE64;TYPE=JPEG:{base64_data}\r\n";
        }

        $vcard .= "END:VCARD\r\n";

        return $vcard;
    }
}

