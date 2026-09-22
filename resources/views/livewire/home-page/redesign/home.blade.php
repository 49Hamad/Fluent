{{--
    Fluent homepage (redesign).
    Section order follows the approved prototype. Sections marked [DB]
    read their content from Filament; the rest is the approved copy.
    The previous homepage view (show-home-page.blade.php) and its
    components are kept in the codebase, untouched.
--}}
<div>
    @include('livewire.home-page.redesign.sections.hero')
    @include('livewire.home-page.redesign.sections.about')
    @include('livewire.home-page.redesign.sections.gap')
    @include('livewire.home-page.redesign.sections.how')
    @include('livewire.home-page.redesign.sections.experience')
    @include('livewire.home-page.redesign.sections.proof')          {{-- [DB] NumberTalk --}}
    @include('livewire.home-page.redesign.sections.partners')       {{-- [DB] Client + OurPartner --}}
    @include('livewire.home-page.redesign.sections.why')
    @include('livewire.home-page.redesign.sections.audience')
    @include('livewire.home-page.redesign.sections.cta')

    {{-- Existing Livewire contact component (same backend: DB + email + Filament notification) --}}
    <livewire:home-page.show-contact-usl-page />
</div>
