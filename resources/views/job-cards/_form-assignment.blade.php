@php
    use App\Enums\JobCardStatus;
@endphp

<div class="asp-form-grid">
    <x-ui.form-field
        label="Assign vehicle to employee"
        for="assigned_to"
        name="assigned_to"
        :ajax="$ajax"
        hint="Active employees at your current branch."
        :col-span="2"
    >
        <x-ui.select id="assigned_to" name="assigned_to" :ajax="$ajax">
            <option value="">Select employee…</option>
            @forelse ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('assigned_to', $jobCard?->assigned_to) == $employee->id)>
                    {{ $employee->displayName() }}
                </option>
            @empty
                <option value="" disabled>No active employees at this branch</option>
            @endforelse
        </x-ui.select>
    </x-ui.form-field>

    @if (($bookings ?? collect())->isNotEmpty())
        <x-ui.form-field label="Booking" for="booking_id" name="booking_id" :ajax="$ajax" hint="Link to an existing booking, if any.">
            <x-ui.select id="booking_id" name="booking_id" :ajax="$ajax">
                <option value="">None</option>
                @foreach ($bookings as $booking)
                    <option value="{{ $booking->id }}" @selected(old('booking_id', $jobCard?->booking_id) == $booking->id)>
                        #{{ $booking->id }}: {{ $booking->customer?->full_name }}
                    </option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>
    @endif

    <x-ui.form-field label="Status" for="status" name="status" :required="true" :ajax="$ajax">
        <x-ui.select id="status" name="status" :ajax="$ajax" required>
            @foreach (JobCardStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $jobCard?->status?->value ?? JobCardStatus::Open->value) == $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </x-ui.select>
    </x-ui.form-field>
</div>
