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
