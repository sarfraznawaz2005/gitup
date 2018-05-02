@extends('gitup::layout.gitup')

@section('title')
    <a href="{{route('__gitup__')}}" class="btn btn-warning btn-sm">
        &larr; Back
    </a>
@endsection

@section('content')

    <form id="frm" action="{{route('gitup_upload')}}" method="post">
        {!! csrf_field() !!}

        <div class="card">
            <div class="card-header bg-success text-white">
                <strong>Files to Upload</strong>
            </div>
            <div class="card-body">
                <table class="table table-condensed table-bordered">
                    <thead>
                    <tr>
                        <th>File</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($uploadFiles as $file)
                        <tr>
                            <td>{{$file}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <hr>

        <div class="card">
            <div class="card-header bg-danger text-white">
                <strong>Files to Delete</strong>
            </div>
            <div class="card-body">
                <table class="table table-condensed table-bordered">
                    <thead>
                    <tr>
                        <th>File</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($deleteFiles as $file)
                        <tr>
                            <td>{{$file}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <hr>

        <div class="card">
            <div class="card-header bg-secondary text-white">
                <strong>Files to Ignore</strong>
            </div>
            <div class="card-body">
                <table class="table table-condensed table-bordered">
                    <thead>
                    <tr>
                        <th>File</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($ignoredFiles as $file)
                        <tr>
                            <td>{{$file}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($uploadFiles)
            <hr>

            <div class="row">
                <div class="col-md-8">&nbsp;</div>
                <div class="col-md-4">
                    <fieldset class="form-group">
                        <legend>
                            Upload
                        </legend>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="server"><strong>Select Server(s):</strong></label><br><br>
                            @foreach($servers as $name => $serverDetails)
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input form-control"
                                               name="server_name[]" id="{{$name}}" value="{{$name}}">
                                        {{ucfirst($name)}}
                                    </label>
                                </div>
                                <hr>
                            @endforeach
                            <button type="submit" class="btn btn-success btn-block">Proceed to Upload</button>
                        </div>
                    </fieldset>
                </div>
            </div>
        @endif

        @foreach($commits as $commit)
            <input type="hidden" name="commits[]" value="{{$commit}}">
        @endforeach

    </form>

@endsection

@push('scripts')

    <script>
        $('#frm').submit(function () {
            if ($('.form-check-input:checked').length <= 0) {
                alert('Please select one or more servers first.');
                return false;
            }
        });
    </script>

    @endsection