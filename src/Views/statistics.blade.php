@extends('gitup::layout.gitup')

@section('title', 'Statistics')

@section('content')
    <div id="piechart"></div>
@endsection

@push('styles')
    <style>
        #piechart {
            width: 100%;
            height: 500px;
        }
    </style>
@endpush

@push('scripts')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {

            var data = google.visualization.arrayToDataTable([
                ['User', 'Total Commits'],
                    @foreach($commits  as $user => $commit)
                ['{{$user}} ({{count($commit)}})', {{count($commit)}}],
                @endforeach
            ]);

            var options = {
                'title': 'Commits Distribution',
                'width': '30%',
                'height': '30%',
                'legend': 'left',
                'chartArea': {
                    left: "0",
                    top: "10%",
                    height: "70%",
                    width: "70%"
                }
            };

            var chart = new google.visualization.PieChart(document.getElementById('piechart'));

            chart.draw(data, options);
        }
    </script>
@endpush