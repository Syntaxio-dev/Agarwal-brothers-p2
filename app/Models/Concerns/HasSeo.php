<?php

namespace App\Models\Concerns;

use App\Support\Seo;

/**
 * Shared SEO accessors. A model defines seoName(), seoFallbackDescription() and
 * seoFallbackImage(); empty seo_title / seo_description / og_image fall back to them.
 */
trait HasSeo
{
    abstract protected function seoName(): string;

    protected function seoFallbackDescription(): ?string
    {
        return null;
    }

    /** Storage path of the model's own image, if any. */
    protected function seoFallbackImage(): ?string
    {
        return null;
    }

    public function seoTitle(): string
    {
        return filled($this->seo_title) ? $this->seo_title : $this->seoName();
    }

    public function seoDescription(): string
    {
        return Seo::description(filled($this->seo_description) ? $this->seo_description : $this->seoFallbackDescription());
    }

    /** Absolute image URL, or null to let the layout use the site default. */
    public function seoImage(): ?string
    {
        return Seo::storage($this->og_image ?: $this->seoFallbackImage());
    }
}
