    {{-- ===== 10. CUSTOMER REVIEWS — auto-stepping carousel ===== --}}
    @if ($reviews->count())
    <section class="overflow-x-clip py-12 sm:py-14">
        <div class="w-[98%] mx-auto md:px-5 lg:px-20">

            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-10">
                <span class="section-badge">
                    Reviews
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    What Our Customers <span class="text-cyan-ink">Say About Us</span>
                </h2>
            </div>

            <div data-reveal="fade" x-data="reviewCarousel(@js($reviews->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'designation' => $r->designation,
                    'organization' => $r->organization,
                    'content' => $r->content,
                    'rating' => $r->rating,
                ])->values()))"
                 @mouseenter="paused = true" @mouseleave="paused = false"
                 class="overflow-hidden -mx-3">

                <div x-ref="track" class="flex"
                     :class="moving ? 'transition-transform duration-700 ease-in-out' : ''"
                     :style="moving ? `transform: translateX(-${step}px)` : ''">
                    <template x-for="review in list" :key="review.id">
                        <div class="basis-full md:basis-1/2 lg:basis-1/3 shrink-0 px-3">
                            <div class="h-full flex flex-col rounded-2xl bg-white border border-gray-100 shadow-sm p-6
                                        hover:shadow-lg hover:border-cyan/20 transition-shadow duration-300">
                                <svg class="h-8 w-8 text-cyan/20 mb-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10H0z"/>
                                </svg>

                                <p class="text-sm text-navy/80 leading-relaxed flex-1" x-text="review.content"></p>

                                <div class="flex gap-0.5 mt-4 mb-4">
                                    <template x-for="s in review.rating" :key="s">
                                        <svg class="h-4 w-4 text-cyan-ink" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </template>
                                </div>

                                <div class="pt-4 border-t border-gray-100 flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-navy flex items-center justify-center shrink-0">
                                        <span class="text-sm font-bold text-white" x-text="review.name.charAt(0)"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-navy truncate" x-text="review.name"></h4>
                                        <p class="text-xs text-slate truncate"
                                           x-text="[review.designation, review.organization].filter(Boolean).join(' · ')"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </section>
    @endif
