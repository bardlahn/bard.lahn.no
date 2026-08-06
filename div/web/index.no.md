---
title: 'Om denne nettsiden'
language: 'no'
routes:
  default: '/no/div/web/'
  canonical: '/no/div/web/'
abstract: 'Denne siden beskriver oppbygningen av Bård Lahns personlige nettside, inkl. informasjon om rettigheter, kildekode, sporing og datahåndtering.'
include-head: assets/bounce-head.php
include-bottom: assets/bounce-bottom.php
---

Denne nettsiden er både et sted å samle mine tekster og publiksasjoner, et teknologisk eksperiment og hobbyprosjekt for min egen del, og et lite bidrag til et internett drevet av mennesker framfor teknologi-giganter. Inspirert av [mange](https://diagram.website) [flotte](https://gossipsweb.net) [folk](https://publicdatalab.org) [der](https://www.are.na/shannon-mattern/poetic-web-i-inqsyxb6o) [ute](https://jonathangray.org) ønsker jeg å bidra til et åpent og [poetisk](https://httpoetics.neocities.org) internett, å fremme [åpne digitale verktøy](https://github.com/bardlahn/academic-open) og standarder, og ikke gi [big tech mer makt](https://attac.no/digital-makt/) og fortjeneste enn nødvendig.

Etter å ha brukt WordPress til å kjøre en personlig hjemmeside og blogg i noen år, valgte jeg i 2026 å bygge min egen nettside fra grunnen av som et lite eksperiment. Inspirert av [Grav](https://getgrav.org) og lignende systemer for innholdshåndtering, endte jeg med å bygge et system som henter innhold fra [Markdown](https://en.wikipedia.org/wiki/Markdown)-filer. Alt innhold på nettsiden ligger i statiske filer som er lesbare og redigerbare uten spesiell programvare, mens en "motor" skrevet i PHP henter filene og viser dem ved hjelp av HTML, CSS og JavaScript.

Å bygge sin egen nettside gir mange muligheter for å <a id="show-overlay" style="text-decoration: underline; cursor: pointer;">leke seg litt</a>. I tillegg kan jeg sørge for at jeg ikke lar noen tredjepart spore brukerne av siden, og at jeg ikke samler inn noen form for personlige data. Siden bruker ikke informasjonskapsler. Jeg bruker [GoatCounter](https://www.goatcounter.com) (åpen kildekode), for å samle grunnleggende statistikk om besøk på siden. (Siden bruker foreløpig Google Fonts som sender IP-adresse og annen grunnleggende informasjon til Google, men uten at dette spores.)

Av andre verktøy bruker jeg [Parsedown](https://parsedown.org) og [Symfony Yaml](https://symfony.com/doc/current/components/yaml.html) for å håndtere Markdown-filer. Jeg har også eksperimentert med [Paper.js](htttps://paperjs.org/), som du kanskje kan finne spor av på denne siden. Full kildekode er tilgjengelig på [Github](https://github.com/bardlahn/bard.lahn.no) for dem som er interessert.

Alt innhold på nettsiden er tilgjengelig under Creative Commons-lisensen [BY-NC-SA](https://creativecommons.org/licenses/by-nc-sa/4.0/), med mindre noe annet er oppgitt. Har du spørsmål om bruk av tekster, bilder eller kode, så <a href="mailto:bard_AT_lahn.no">ta gjerne kontakt</a>.

Bildene på nettsiden er stort sett tatt av meg, med følgende unntak:
- Ameriflux-tårnet - [Kyle Spradley](https://www.kspradleyphoto.com) via Flickr.com (CC BY-NC)
- Camp-brannen (satellitbilde) - Joshua Stevens, [NASA](https://science.nasa.gov/earth/earth-observatory/camp-fire-rages-in-california-144225/) (CC0)
- Fosselven kraftverk - [Normann Helger](https://kulturnav.org/c70ae073-67ac-4467-806c-de7164ff50be) / Anno Domkirkeodden (CC0)
- Oljeindustri, diverse bilder - [Offshore Norge](https://www.flickr.com/photos/olfnorge/) via Flickr.com (CC BY-SA)
- [Portretter av meg](/:$lang:/bio/img/) - se oppgitt fotograf