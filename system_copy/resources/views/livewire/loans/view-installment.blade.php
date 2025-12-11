<div class=" flex flex-col w-full">
{{--    <div class=" flex rounded-md p-2 mt-4 justify-end ">--}}
{{--        <button wire:click="createPDF" type="button" class=" bg-gray-200 p-2 rounded-md hover:bg-green-200 hover:text-black">Export PDF</button>--}}
{{--    </div>--}}
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
