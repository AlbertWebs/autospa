@php $campaign = $campaign ?? null; @endphp

<x-ui.form-section title="Email Campaign" description="Subject, body content, status, and scheduling.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Campaign Name" for="name" name="name" :required="true" :col-span="2">
            <x-ui.input id="name" name="name" :value="old('name', $campaign->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Subject" for="subject" name="subject" :required="true" :col-span="2">
            <x-ui.input id="subject" name="subject" :value="old('subject', $campaign->subject ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Email Body" for="body" name="body" :required="true" :col-span="2">
            <x-ui.textarea id="body" name="body" rows="8" required>{{ old('body', $campaign->body ?? '') }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field label="Status" for="status" name="status" :required="true">
            <x-ui.select id="status" name="status" required>
                @foreach (['draft', 'scheduled', 'sent', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $campaign->status ?? 'draft') == $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Scheduled At" for="scheduled_at" name="scheduled_at">
            <x-ui.input id="scheduled_at" name="scheduled_at" type="datetime-local" :value="old('scheduled_at', isset($campaign->scheduled_at) ? $campaign->scheduled_at->format('Y-m-d\TH:i') : '')" />
        </x-ui.form-field>
    </div>
</x-ui.form-section>
