<x-ui.form-field label="Notes" for="notes" name="notes" :col-span="2" :ajax="$ajax">
    <x-ui.textarea id="notes" name="notes" rows="4" :ajax="$ajax" placeholder="Damage notes, special requests, bay instructions…">{{ old('notes', $jobCard?->notes) }}</x-ui.textarea>
</x-ui.form-field>
