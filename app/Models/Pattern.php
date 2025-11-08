<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Hoa\Compiler\Llk\Llk;
use Hoa\File\Read;
use Hoa\Regex\Visitor\Isotropic;
use Hoa\Math\Sampler\Random;

class Pattern extends Model
{
    use HasFactory, SoftDeletes;

    public const ACCESS_PUBLIC = 'public';
    public const ACCESS_AUTHENTICATED = 'authenticated';
    public const ACCESS_SUPER_ADMIN = 'super_admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'group',
        'pattern',
        'responses',
        'callback',
        'severity',
        'priority',
        'enabled',
        'stop_processing',
        'access_level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'responses' => 'array',
        'enabled' => 'boolean',
        'priority' => 'integer',
        'hit_count' => 'integer',
        'last_used_at' => 'datetime',
        'stop_processing' => 'boolean',
        'access_level' => 'string',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_used_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['readable_pattern'];

    /**
     * Determine whether this pattern can be accessed by the given user.
     *
     * @param  \App\Models\User|null  $user
     * @return bool
     */
    public function canBeAccessedBy(?\App\Models\User $user): bool
    {
        $level = $this->access_level ?? self::ACCESS_PUBLIC;

        switch ($level) {
            case self::ACCESS_PUBLIC:
                return true;

            case self::ACCESS_AUTHENTICATED:
                return $user !== null;

            case self::ACCESS_SUPER_ADMIN:
                if ($user === null) {
                    return false;
                }

                if ($user->can('Super Admin')) {
                    return true;
                }

                return false;
            default:
                return false;
        }
    }

    /**
     * Scope a query to patterns accessible to the given user.
     *
     * Usage: Pattern::accessibleTo($user)->get()
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\User|null  $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccessibleTo($query, ?\App\Models\User $user = null)
    {
        if ($user === null) {
            return $query->where('access_level', self::ACCESS_PUBLIC);
        }

        if ($user->hasRole('Super Admin')) {
            return $query;
        }

        return $query->whereIn('access_level', [self::ACCESS_PUBLIC, self::ACCESS_AUTHENTICATED]);
    }

    /**
     * Get the human-readable version of the pattern.
     *
     * @return ?string
     */
    public function getReadablePatternAttribute(): ?string
    {
        if (empty($this->pattern)) {
            return null;
        }

        try {
            $grammar = new Read('hoa://Library/Regex/Grammar.pp');

            $compiler = Llk::load($grammar);

            $ast = $compiler->parse($this->pattern);

            $generator = new Isotropic(new Random());

            $generatedString = $generator->visit($ast);

            return $this->cleanupGeneratedString($generatedString);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Cleans up the raw output from the regex generator.
     *
     * @param string $string
     * @return ?string
     */
    private function cleanupGeneratedString(string $string): ?string
    {
        if (empty($string)) {
            return null;
        }

        $string = str_replace(["\xC2\xA0", "\xA0", "\u{00A0}"], ' ', $string);
        $string = preg_replace('/[[:cntrl:]]+/u', '', $string) ?? $string;

        $previous = null;
        $maxIterations = 5;
        $i = 0;

        while ($string !== $previous && $i++ < $maxIterations) {
            $previous = $string;

            if (preg_match('#^/(.*)/([a-zA-Z]*)$#u', $string, $m)) {
                $string = $m[1];
            }

            $string = preg_replace('/\\\\b/u', '', $string) ?? $string;
            $string = preg_replace('/(^|\W)b(?=\w)/iu', '$1', $string) ?? $string;
            $string = preg_replace('/(?<=\w)b($|\W)/iu', '$1', $string) ?? $string;
            $string = preg_replace('/^\/+|\/+$/u', '', $string) ?? $string;
            $string = preg_replace('/\s+/u', ' ', $string) ?? $string;
            $string = trim($string);
        }

        return empty($string) ? null : $string;
     }
}
