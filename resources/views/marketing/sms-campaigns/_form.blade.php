@php $campaign = $campaign ?? null; @endphp

<x-ui.form-section title="SMS Campaign" description="Campaign name, message content, and scheduling.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Campaign Name" for="name" name="name" :required="true" :col-span="2">
            <x-ui.input id="name" name="name" :value="old('name', $campaign->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Message" for="message" name="message" :required="true" :col-span="2">
            <x-ui.textarea id="message" name="message" rows="4" required>{{ old('message', $campaign->message ?? '') }}</x-ui.textarea>
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
