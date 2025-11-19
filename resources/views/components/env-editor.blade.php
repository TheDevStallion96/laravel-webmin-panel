<div x-data="{ rows: [{k:'APP_ENV', v:'production'}] }" class="space-y-2">
    <template x-for="(row, i) in rows" :key="i">
        <div class="grid grid-cols-5 gap-2 items-center">
            <input class="border rounded p-2 col-span-2" x-model="row.k" placeholder="KEY" />
            <input class="border rounded p-2 col-span-3" x-model="row.v" :name="row.k ? 'environment['+row.k+']' : ''" placeholder="value" />
        </div>
    </template>
    <button type="button" class="text-xs px-2 py-1 border rounded" x-on:click="rows.push({k:'', v:''})">Add Variable</button>
</div>
