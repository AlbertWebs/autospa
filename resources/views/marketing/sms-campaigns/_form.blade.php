@php $campaign = $campaign ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Campaign Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('name', $campaign->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="message" value="Message" />
        <textarea id="message" name="message" rows="4" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>{{ old('message', $campaign->message ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('message')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            @foreach (['draft', 'scheduled', 'sent', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(old('status', $campaign->status ?? 'draft') == $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
    <div>
        <x-input-label for="scheduled_at" value="Scheduled At" />
        <x-text-input id="scheduled_at" name="scheduled_at" type="datetime-local" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('scheduled_at', isset($campaign->scheduled_at) ? $campaign->scheduled_at->format('Y-m-d\TH:i') : '')" />
        <x-input-error :messages="$errors->get('scheduled_at')" />
    </div>
</div>
