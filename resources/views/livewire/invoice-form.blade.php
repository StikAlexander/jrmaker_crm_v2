<div>
    <form wire:submit.prevent="save">
        <div>
            <label for="invoice_number">Invoice Number</label>
            <input type="text" id="invoice_number" wire:model.defer="invoice_number" />
        </div>

        <div>
            <label for="issue_date">Issue Date</label>
            <input type="date" id="issue_date" wire:model.defer="issue_date" />
        </div>

        <div>
            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" wire:model.defer="due_date" disabled />
        </div>

        <div>
            <label for="total_amount">Total Amount</label>
            <input type="number" id="total_amount" wire:model="total_amount" step="0.01" />
        </div>

        <div>
            <label for="total_paid">Total Paid</label>
            <input type="number" id="total_paid" wire:model="total_paid" step="0.01" />
        </div>

        <div>
            <label for="pending_amount">Pending Amount</label>
            <input type="number" id="pending_amount" wire:model="pending_amount" step="0.01" disabled />
        </div>

        <div>
            <label for="status">Status</label>
            <input type="text" id="status" wire:model="status" disabled />
        </div>

        <button type="submit">Save</button>
    </form>
</div>