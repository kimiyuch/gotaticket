<?php

return [
	'accepted' => ':Attribute muss akzeptiert werden.',
	'after' => ':Attribute muss ein Datum nach :date sein.',
	'after_or_equal' => ':Attribute muss ein Datum nach oder gleich :date sein.',
	'array' => ':Attribute muss eine Liste sein.',
	'before' => ':Attribute muss ein Datum vor :date sein.',
	'before_or_equal' => ':Attribute muss ein Datum vor oder gleich :date sein.',
	'between' => [
		'array' => ':Attribute muss zwischen :min und :max Elemente haben.',
		'numeric' => ':Attribute muss zwischen :min und :max liegen.',
		'string' => ':Attribute muss zwischen :min und :max Zeichen lang sein.',
	],
	'boolean' => ':Attribute muss ja oder nein sein.',
	'confirmed' => 'Die Bestätigung von :attribute stimmt nicht überein.',
	'current_password' => 'Das Passwort ist falsch.',
	'date' => ':Attribute muss ein gültiges Datum sein.',
	'date_format' => ':Attribute muss dem Format :format entsprechen.',
	'different' => ':Attribute und :other müssen sich unterscheiden.',
	'email' => ':Attribute muss eine gültige E-Mail-Adresse sein.',
	'exists' => 'Die Auswahl für :attribute ist ungültig.',
	'filled' => ':Attribute muss ausgefüllt sein.',
	'gt' => [
		'numeric' => ':Attribute muss grösser als :value sein.',
	],
	'gte' => [
		'numeric' => ':Attribute muss grösser oder gleich :value sein.',
	],
	'in' => 'Die Auswahl für :attribute ist ungültig.',
	'integer' => ':Attribute muss eine ganze Zahl sein.',
	'lowercase' => ':Attribute darf nur Kleinbuchstaben enthalten.',
	'lt' => [
		'numeric' => ':Attribute muss kleiner als :value sein.',
	],
	'lte' => [
		'numeric' => ':Attribute muss kleiner oder gleich :value sein.',
	],
	'max' => [
		'array' => ':Attribute darf höchstens :max Elemente haben.',
		'numeric' => ':Attribute darf höchstens :max sein.',
		'string' => ':Attribute darf höchstens :max Zeichen lang sein.',
	],
	'min' => [
		'array' => ':Attribute muss mindestens :min Elemente haben.',
		'numeric' => ':Attribute muss mindestens :min sein.',
		'string' => ':Attribute muss mindestens :min Zeichen lang sein.',
	],
	'not_in' => 'Die Auswahl für :attribute ist ungültig.',
	'numeric' => ':Attribute muss eine Zahl sein.',
	'password' => [
		'letters' => ':Attribute muss mindestens einen Buchstaben enthalten.',
		'mixed' => ':Attribute muss Gross- und Kleinbuchstaben enthalten.',
		'numbers' => ':Attribute muss mindestens eine Zahl enthalten.',
		'symbols' => ':Attribute muss mindestens ein Sonderzeichen enthalten.',
		'uncompromised' => 'Dieses Passwort ist in einem Datenleck aufgetaucht. Bitte wähle ein anderes.',
	],
	'regex' => 'Das Format von :attribute ist ungültig.',
	'required' => ':Attribute fehlt.',
	'required_if' => ':Attribute fehlt.',
	'required_with' => ':Attribute fehlt.',
	'same' => ':Attribute und :other müssen übereinstimmen.',
	'size' => [
		'string' => ':Attribute muss genau :size Zeichen lang sein.',
	],
	'string' => ':Attribute muss ein Text sein.',
	'unique' => ':Attribute ist schon vergeben.',
	'url' => ':Attribute muss eine gültige URL sein.',

	'custom' => [
		'email' => [
			'unique' => 'Mit dieser E-Mail gibt es schon ein Konto. Logge dich ein oder setze dein Passwort zurück.',
		],
	],

	'attributes' => [
		'name' => 'Name',
		'email' => 'E-Mail',
		'password' => 'Passwort',
		'current_password' => 'Aktuelles Passwort',
		'phone' => 'Telefon',
		'opponent' => 'Gegner',
		'kickoff_at' => 'Anpfiff',
		'competition' => 'Wettbewerb',
		'code' => 'Code',
		'kind' => 'Art',
		'sort' => 'Reihenfolge',
	],
];
