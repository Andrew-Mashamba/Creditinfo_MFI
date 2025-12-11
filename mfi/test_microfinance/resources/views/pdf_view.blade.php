<div class=" flex flex-col w-full">
    <div class=" flex w-full mt-6">
        <table class="w-full table border-collapse border rounded-lg">
            <thead class="bg-gray-100">
            <tr>
                <th class="py-2 px-3 text-left">Installment</th>
                <th class="py-2 px-3 text-left">Interest</th>
                <th class="py-2 px-3 text-left">Principle</th>
                <th class="py-2 px-3 text-left">Balance</th>
            </tr>
            </thead>
            <tbody>
            @foreach($installments as $installment)
                <tr>
                    <td class="border py-2 px-3">{{ $installment->installment }}</td>
                    <td class="border py-2 px-3">{{ $installment->interest }}</td>
                    <td class="border py-2 px-3"> {{ $installment->principle }}</td>
                    <td class="border py-2 px-3"> {{ $installment->balance }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
</div>
