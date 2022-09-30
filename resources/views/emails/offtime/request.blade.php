@component('mail::message')
    # Request Day Off / Vacation <br>

    Name: {{$full_name}}
    Date: {{$start_day}} - {{$end_day}}
    Type: {{$type}}
    {{$link}}

@endcomponent
