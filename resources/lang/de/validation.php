<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License,version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
return [
    /*
     * Language Rules
     */

    "accepted" => ":attribute muss akzeptiert werden.",
    "active_url" => ":attribute ist keine g&uuml;tige URL.",
    "after" => ":attribute muss ein Datum nach :date sein.",
    "alpha" => ":attribute darf nur Buchstaben enthalten.",
    "alpha_dash" => ":attribute darf nur Bustaben, Ziffern und Bindestriche enthalten.",
    "alpha_num" => ":attribute darf nur Bustaben und Ziffern enthalten.",
    "array" => ":attribute muss ein Feld sein.",
    "before" => ":attribute muss vor :date sein.",
    "between" => [
	"numeric" => ":attribute muss zwischen :min und :max sein.",
	"file" => ":attribute muss zwischen :min und :max kilobytes haben.",
	"string" => ":attribute muss zwischen :min und :max Zeichen haben.",
	"array" => ":attribute muss zwischen :min und :max Elementen haben.",
    ],
    "confirmed" => ":attribute Best&auml;tigung stimmt nicht &uuml;berein.",
    "date" => ":attribute ist kein g&uuml;ltiges Datum.",
    "date_format" => ":attribute hat nicht das Format :format.",
    "different" => ":attribute und :other m&uuml;ssen sich unterscheiden.",
    "digits" => ":attribute muss :digits Ziffern enthalten.",
    "digits_between" => ":attribute muss zwischen :min und :max Ziffern enthalten.",
    "email" => ":attribute muss eine g&uuml;ltige E-Mail Adresse sein.",
    "exists" => ":attribute ist ung&uuml;ltig.",
    "image" => ":attribute muss ein Bild sein.",
    "in" => ":attribute ist ung&uuml;ltig.",
    "integer" => ":attribute muss ganzzahlig sein.",
    "ip" => ":attribute muss einge g&uuml;ltige IP-Adresse sein.",
    "max" => [
	"numeric" => ":attribute darf nicht gr&ouml;&szlig;er als :max sein.",
	"file" => ":attribute darf nicht gr&ouml;&szlig;er als :max kilobytes sein.",
	"string" => ":attribute darf nicht l&auml;nger als :max Zeichen sein.",
	"array" => ":attribute darf nicht mehr als :max Elemente haben.",
    ],
    "mimes" => ":attribute muss eine Datei vom Typ :values sein.",
    "min" => [
	"numeric" => ":attribute muss mindestens :min sein.",
	"file" => ":attribute muss mindestens :min kilobytes gro&szlig; sein.",
	"string" => ":attribute muss mindestens :min Zeichen enthalten.",
	"array" => ":attribute muss mindestens :min Elemente enthalten.",
    ],
    "not_in" => ":attribute ist ung&uuml;ltig.",
    "numeric" => ":attribute muss numerisch sein.",
    "regex" => ":attribute Format ung&uuml;ltig.",
    "required" => ":attribute ist Pflichtfeld.",
    "required_if" => ":attribute ist Pflichtfeld, wenn :other ist :value.",
    "required_with" => ":attribute ist Pflichtfeld, wenn :values gegeben ist.",
    "required_without" => ":attribute ist Pflichtfeld, wenn :values nicht gegeben ist.",
    "required_without_all" => ":attribute ist Pflichtfeld, wenn keines von :values gegeben sind.",
    "same" => ":attribute und :other m&uuml;ssen &uuml;bereinstimmen.",
    "size" => [
	"numeric" => ":attribute muss :size sein.",
	"file" => ":attribute muss :size kilobytes gro&szlig; sein.",
	"string" => ":attribute muss :size Zeichen enthalten.",
	"array" => ":attribute muss :size Elemente enthalten.",
    ],
    "unique" => ":attribute wird bereits verwendet.",
    "url" => ":attribute Format ung&uuml;ltig.",
    "tastingnumber_nr_unique" => ":attribute wird bereits verwendet",
    "tastingnumber_wine_exists" => ":attribute ist ung&uuml;ltig",
    "tastingnumber_wine_unique" => ":attribute wurde bereits zugewiesen",
];
