@extends('gitup::layout.gitup')

@section('title', 'Commits')

@section('header')
    @if ($isDirty)
        <a href="{{route('gitup_files')}}" class="btn btn-warning btn-sm">
            <i class="fa fa-history"></i> View Un-Committed Files
        </a>
    @else
        <span class="badge badge-success"><i class="fa fa-smile-o"></i> CLEAN</span>
    @endif
@endsection

@section('content')

    <form id="frmUpload" action="{{route('gitup_preview')}}" method="post">
        {!! csrf_field() !!}

        <table class="table table-condensed table-bordered">
            <thead>
            <tr>
                <th>User</th>
                <th>Commit ID</th>
                <th>Date</th>
                <th>Message</th>
                <th>Status</th>
                <th style="text-align: center;">Action</th>
                <th style="text-align: center;">
                    <input type="checkbox" id="checkAll">
                </th>
            </tr>
            </thead>

            <tfoot>
            <tr>
                <th>User</th>
                <th>Commit ID</th>
                <th>Date</th>
                <th>Message</th>
                <th>Status</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
            </tfoot>

            <tbody>
            @foreach($commits as $commit)

                <?php

                $uServers = [];
                $isUploaded = false;
                $status = '<span class="badge badge-danger">Pending</span>';

                if (in_array($commit['commit_id'], $uploadedCommits)) {

                    $uServers = collect(DB::table('commits')
                        ->where('commit_id', $commit['commit_id'])
                        ->get(['server']))
                        ->pluck('server')
                        ->toArray();

                    $uServers = array_filter($uServers);
                    $uServers = array_map('ucfirst', $uServers);
                    $uServers = array_unique($uServers);                    

                    if (count($uServers) === count(config('gitup.servers'))) {
                        $status = '<span class="badge badge-success">Uploaded</span>';
                        $isUploaded = true;
                    } else {
                        $status = '<span class="badge badge-warning">' . implode(' | ', $uServers) . '</span>';
                    }
                }

                ?>

                <tr style="background:{{Carbon\Carbon::parse(str_replace('-', '/', $commit['date']))->isToday() ? '#d3ffdd' :''}}">
                    <td style="width: 150px;">{{$commit['user']}}</td>
                    <td style="width: 100px;">{{$commit['commit_id']}}</td>
                    <td style="width: 150px;">{{$commit['date']}}</td>
                    <td>{{$commit['message']}}</td>
                    <td style="text-align: center; width: 100px;" title="{{implode(' | ', $uServers)}}">{!! $status !!}</td>
                    <td style="text-align: center; width: 1px;">
                        <a href="{{route('gitup_files', $commit['commit_id'])}}"
                           data-toggle="tooltip"
                           data-placement="top"
                           title="View Files">
                            <b class="btn btn-success btn-sm fa fa-eye"></b>
                        </a>
                    </td>
                    <td style="text-align: center; width: 30px;">
                        @if(!$isUploaded)
                            <input type="checkbox" name="commits[]" class="chkUpload" value="{{$commit['commit_id']}}">
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>

        <br><br>

        <div class="row">
            <div class="col-md-9">&nbsp;</div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-success btn-block">Upload Selected</button>
            </div>
        </div>

    </form>
@endsection

@push('scripts')
    <script>
        var table = $('.table').DataTable({
            "order": [],
            "responsive": true,
            "pageLength": 25,
            "autoWidth": false,
            aoColumnDefs: [
                {
                    bSortable: false,
                    aTargets: [-1, -2]
                }
            ]
        });

        ///////////////////////////////////////////////////////////////
        // filter columns
        var dates = [];

        $(".table tfoot th").slice(0, 5).each(function (i) {
            var select = $('<select style="width: 100%;"><option value=""></option></select>')
                .appendTo($(this).empty())
                .on('change', function () {
                    table.column(i)
                        .search($(this).val(), true, false)
                        .draw();
                });

            table.column(i).data().unique().sort().each(function (d, j) {
                var val = d;

                // remove html
                if (i === 4) {
                    val = $(val).text().replace(/\s/g, '');
                }

                // remove time in case of date column
                if (i === 2) {
                    val = d.split(' ')[0];

                    if (jQuery.inArray(val, dates) !== -1) {
                        // continue
                        return true;
                    }

                    dates.push(val);

                    // we will populate date column later with dates in descending order
                    return true;
                }

                select.append('<option value="' + val + '">' + val + '</option>')
            });
        });

        // populate dates select box
        $(dates).sort(function (a, b) {
            return a > b ? -1 : a < b ? 1 : 0;
        }).each(function (i, v) {
            $('tfoot select:eq(2)').append('<option value="' + v + '">' + v + '</option>')
        });

        // put filters on header
        $('tfoot').css('display', 'table-header-group');
        ///////////////////////////////////////////////////////////////

        $('#checkAll').click(function () {
            var checked = this.checked;

            $('.dataTable .chkUpload').each(function () {
                this.checked = checked;

                if (this.checked) {
                    $(this).closest('tr').addClass('warning');
                }
                else {
                    $(this).closest('tr').removeClass('warning');
                }
            });
        });

        $(document).on('click', '.chkUpload', function () {
            if (this.checked) {
                $(this).closest('tr').addClass('warning');
            }
            else {
                $(this).closest('tr').removeClass('warning');
            }
        });

        $('#frmUpload').submit(function () {
            if (!$('.chkUpload:checked').length) {
                alert('Please select commit(s) first!');
                return false;
            }

            $(this).submit();
        });

    </script>
@endpush
