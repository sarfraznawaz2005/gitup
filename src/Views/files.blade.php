@extends('gitup::layout.gitup')

@section('title')
    <a href="{{route('__gitup__')}}" class="btn btn-warning btn-sm">
        <i class="fa fa-arrow-left"></i> Back
    </a>
@endsection

@section('content')
    <table class="table table-condensed table-bordered">
        <thead>
        <tr>
            <th>Status</th>
            <th>File</th>
        </tr>
        </thead>

        <tfoot>
        <tr>
            <th>Status</th>
            <th>&nbsp;</th>
        </tr>
        </tfoot>

        <tbody>
        @foreach($files as $file)

            @if (! trim($file['file']))
                @continue
            @endif

            <?php
            $color = 'secondary';

            if ($file['status'] === 'A') {
                $color = 'success';
            } elseif ($file['status'] === 'M') {
                $color = 'primary';
            } elseif ($file['status'] === 'D') {
                $color = 'danger';
            }
            ?>
            <tr>
                <td style="text-align: center; width: 1px;">
                    <span class="badge badge-{{$color}}">{{$file['status']}}</span>
                </td>
                <td>{{$file['file']}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr>

    <fieldset class="form-group">
        <legend>Diff Log</legend>
        <div class="difflog">
            {!! $diffLog !!}
        </div>
    </fieldset>
@endsection

@push('styles')
    <style>
        .difflog {
            overflow: auto;
            max-height: 800px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        var table = $('.table').DataTable({
            "order": [0, 'asc'],
            "responsive": true,
            "pageLength": 1000,
            "autoWidth": false,
            aoColumnDefs: [
                {
                    bSortable: false,
                    aTargets: [-1]
                }
            ]
        });

        // filter columns
        $(".table tfoot th:not(:last)").each(function (i) {
            var select = $('<select style="width: 100%;"><option value=""></option></select>')
                .appendTo($(this).empty())
                .on('change', function () {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());

                    table.column(i)
                        .search(val ? '^' + val + '$' : '', true, false)
                        .draw();
                });

            table.column(i).data().unique().sort().each(function (val, idx) {
                // remove html in case of first/type column
                if (i === 0) {
                    val = $(val).text().replace(/\s/g, '');
                }

                select.append('<option value="' + val + '">' + val + '</option>')
            });
        });

        // put filters on header
        $('tfoot').css('display', 'table-header-group');
    </script>
@endpush