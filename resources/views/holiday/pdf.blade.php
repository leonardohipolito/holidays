<div>
    <h1>{{ $holiday->title }}</h1>
    <p>
        {{ $holiday->description }}
    </p>
    <dl>
        <dt>Date</dt>
        <dd>{{ $holiday->date }}</dd>
        <dt>Location</dt>
        <dd>{{ $holiday->location }}</dd>
        <dt>Participants</dt>
        <dd>
            <ul>
                @foreach($holiday->participants as $participant)
                    <li>{{ $participant->name }}</li>
                @endforeach
            </ul>
        </dd>
    </dl>
</div>
