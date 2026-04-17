Aquest és el **Mega README Arquitectònic** definitiu per al teu repositori. Està dissenyat des de la perspectiva d'un Arquitecte de Software Sènior, explicant absolutament tot el projecte: l'arquitectura, el disseny de base de dades, el flux del frontend, les mecàniques de joc, l'API i la guia de desplegament.

Pots copiar tot aquest bloc i enganxar-lo directament al teu fitxer `README.md` a l'arrel del teu projecte. Prepara't, perquè és un manual de primer nivell\!



#  XOXEMONS - Documentació Tècnica i Arquitectura (Mega README)

Benvinguts al repositori oficial de **Xoxemons**, una aplicació web Full-Stack moderna, interactiva i altament reactiva. Aquest projecte fusiona el col·leccionisme de criatures (a l'estil clàssic) amb mecàniques PVP (Jugador contra Jugador) de risc-recompensa, gestió d'inventaris complexos i interaccions socials en temps real.

Aquest document actua com la **Bíblia del Projecte**, detallant des de les decisions d'arquitectura d'alt nivell fins als algorismes interns que controlen l'economia i l'equilibri del joc.



##  Taula de Continguts

1.  [Visió General i Filosofia del Projecte]()
2.  [Arquitectura del Sistema (Tech Stack)]()
3.  [Mecàniques de Joc i Lògica de Negoci]()
4.  [Estructura del Frontend (Angular)]()
5.  [Estructura del Backend (Laravel)]()
6.  [Esquema de Base de Dades (Relacional)]()
7.  [Documentació de l'API REST]()
8.  [Desplegament i Entorns (Docker)]()
9.  [Seguretat i Protecció]()
10. [Roadmap i Futures Millores]()



## 1\.  Visió General i Filosofia del Projecte

**Xoxemons** no és només un CRUD bàsic; és un ecosistema digital on les dades tenen pes i conseqüències. L'objectiu d'aquest projecte és demostrar el domini sobre estructures de dades complexes (taules pivot, relacions N:M), reactivitat avançada al frontend (RxJS) i disseny d'una API RESTful robusta.

### Principis de Disseny

  * **Single Source of Truth (SSOT):** Tant al backend com al frontend (via `BehaviorSubjects`), les dades resideixen en un únic punt per evitar inconsistències visuals.
  * **Fail-Fast & Graceful Degradation:** L'aplicació intercepta errors (ex: tokens expirats) abans que arribin a trencar la UI, expulsant l'usuari proactivament.
  * **Game Feel & Optimistic UI:** Simulem temps de càrrega o llancem animacions abans d'esperar respostes del servidor (ex: tirada de daus al PVP) per fer que l'experiència se senti viva i ràpida.





## 2\.  Arquitectura del Sistema (Tech Stack)

El sistema segueix un model **Client-Servidor Desacoblat**. El frontend i el backend viuen en contenidors separats i només es comuniquen mitjançant HTTP i respostes JSON estandarditzades.

###  Frontend (Capa de Presentació i Estat)

  * **Framework:** Angular 17+ (Estricte, Standalone Components). Evitem els antics `NgModules` per guanyar velocitat de compilació i simplicitat.
  * **Reactivitat:** RxJS intensiu (`BehaviorSubject`, `switchMap`, `debounceTime`) per a la gestió global de l'estat.
  * **Control de Flux:** Utilització de la nova sintaxi `@if`, `@for` (amb `track`) per maximitzar el rendiment del DOM.
  * **Formularis:** Híbrid estratègic entre `ReactiveFormsModule` (per registres i operacions complexes d'admin) i `FormsModule` clàssic (per manipulacions d'estat lleugeres).

### Backend (Capa de Lògica i Persistència)

  * **Framework:** Laravel 11.
  * **Autenticació:** Laravel Sanctum / JWT (JSON Web Tokens) amb protecció mitjançant Bearer Tokens.
  * **ORM:** Eloquent, fent un ús massiu de *Eager Loading* (`with()`) per resoldre el problema de les consultes N+1.
  * **BBDD:** MySQL 8.0 estructurat de forma altament normalitzada.





## 3\.  Mecàniques de Joc i Lògica de Negoci

Aquesta secció detalla com funcionen els engranatges interns del joc.

###  Inventari Matemàtic

L'inventari no és una simple llista. Simula una motxilla clàssica de videojocs:

  * **Mida fixa:** Sempre té 20 slots (espais) visuals.
  * **Apilament (Stacking):** Si un objecte és apilable (ex: `is_stackable = true`, com les xuxes), es divideix algorítmicament en blocs de 5. Si l'usuari té 12 xuxes, el codi ocuparà 3 slots visuals (un de 5, un altre de 5, i un de 2).
  * **Consumibles Únics:** Les vacunes ocupen sempre 1 slot sencer per unitat, obligant l'usuari a gestionar l'espai.

###  Evolucions i Malalties

Els Xuxemons tenen un cicle de vida dictaminat pel que mengen:

1.  **Fases de Creixement:** Petit (requereix 3 xuxes) -\> Mitjà (requereix 5 xuxes) -\> Gran (top-tier).
2.  **Sistema de Malalties (RNG):** Alimenta un Xuxemon té risc. El backend tira uns daus invisibles segons els percentatges globals (`atracon`, `sobredosis`, `bajon`).
3.  **Modificadors Actius:** Si un Xuxemon contrau "Bajón de azúcar", el seu requisit per evolucionar augmenta automàticament en +2 xuxes. Aquestes malalties només es curen consumint una "Vacuna" de l'inventari.

###  Combat Asíncron de Risc/Recompensa (PVP)

Aquesta és la mecànica estrella (component `Battle`). Si lluites i guanyes, *robes* el Xuxemon de l'amic. Si perds, *ell et roba el teu*.
El càlcul del guanyador mescla tres vectors de dades:

1.  **RNG Base:** Tirada de dau aleatòria d'1 a 6.
2.  **Rol (Mida):** +1 punt si és Mitjà, +2 punts si és Gran.
3.  **Elements (Pedra-Paper-Tisora):** Aigua \> Terra, Terra \> Aire, Aire \> Aigua. Dóna un +1/-1 a l'estat final.

###  Xat i Comunicació (Short Polling)

Per simular comunicació en temps real sense la infraestructura de WebSockets, s'utilitza una arquitectura de **Short Polling** al component de xat amb RxJS:
S'obre un cicle `setInterval` de 2000ms que escaneja la BBDD per missatges nous. És **crític** que aquest bucle es destrueixi (`clearInterval`) al `ngOnDestroy` per prevenir fuites de memòria (Memory Leaks).








## 4\.  Estructura del Frontend (Angular)

El projecte frontend està dissenyat seguint el patró "Smart/Dumb Components" i injecció de dependències a nivell d'arrel.

### Mapa de Directoris

```text
frontend/src/app/
├── app.config.ts        # Ruter principal i Interceptors globals HTTP
├── app.html & app.ts    # Component "closca" arrel (<router-outlet>)
├── components/          # Vistes Principals (Smart Components)
│   ├── admin/           # Dashboard (Només rol 'robot')
│   ├── battle/          # Motor lògic i UI de combats PVP
│   ├── chat/            # UI de missatgeria directa i auto-scroll
│   ├── friends/         # Cercador reactiu d'usuaris
│   ├── inventory/       # Motor d'apilament i UI de motxilla
│   ├── loading/         # Overlay global de càrrega de z-index alt
│   ├── login/ & register/ # Portes d'accés (Auth)
│   ├── main/            # Hub del jugador i recompensa diària
│   ├── profile/         # Actualització i eliminació en cascada de comptes
│   └── xuxedex/         # Modals d'alimentació, cura i llistat de criatures
├── guards/              # Capa de Seguretat de Navegador
│   ├── admin.guard.ts   # Double-check (backend + front) de permisos
│   └── auth-guard.ts    # Single-check del Bearer token local
├── interceptors/        
│   └── auth.interceptor.ts # "Middleware" que injecta el JWT a cada crida HTTP
└── services/            # Font Única de Veritat (Singleton API Wrappers)
    ├── auth.ts          # Cicle de vida de sessió i token
    ├── battle.service.ts # Resolució de robatoris i RNG
    ├── chat.service.ts  # Historial de missatges
    ├── friend.service.ts # Gestió d'estat reactiu de sol·licituds
    ├── inventory.service.ts # BehaviorSubject per la motxilla
    ├── loading.ts       # BehaviorSubject pel loader global
    └── xuxemon.service.ts # API del core del joc
```

### El Poder dels `BehaviorSubjects`

La majoria dels serveis (com `inventory.service.ts` o `friend.service.ts`) no retornen dades als components. Retornen el flux sencer. Això significa que quan un usuari consumeix una vacuna al modal de la *Xuxedex*, l'inventari s'actualitza i qualsevol component escoltant l'inventari (com la vista *Inventory*) redibuixa el DOM al moment, garantint una sincronització del 100%.







## 5\. ⚙️ Estructura del Backend (Laravel)

El backend utilitza l'estàndard MVC, però posant molt èmfasi en les **Taules Pivot** (Models Intermedis) degut a la naturalesa col·leccionista del joc.

### Taules Pivot Essencials

No n'hi ha prou en dir "L'usuari 1 té el Xuxemon 5 (Pikachu)". Hem de saber quant ha menjat *aquell Pikachu en concret*, si està malalt o quina talla té actualment. Això ho aconseguim donant "vida" a les taules pivot:

  * `UserXuxemon` (Pivot modelat explícitament): Conté camps extres com `food_eaten`, `size` i `disease`.
  * `UserItem` (Pivot): Conté `quantity` per saber la quantitat d'un objecte apilable en possessió.

### Controladors Clau

  * **`AuthController.php`:** Gestiona el registre, generació del `custom_id` d'usuari i expedició de tokens Sanctum.
  * **`XuxemonController.php` & `InventoryController.php`:** Serveixen els objectes de l'usuari actualitzats, calculen les evolucions basant-se en l'espècie de la criatura i actualitzen l'inventari.
  * **`BattleController.php`:** Orquestra la transferència de propietat (`transfer`). Modifica el propietari a la taula pivot d'un jugador perdedor cap al jugador guanyador en una sola transacció de base de dades.
  * **`AdminController.php`:** Rutes protegides per un middleware estricte que permet injectar objectes o Xuxemons als usuaris i alterar la taula `settings`.







## 6\.  Esquema de Base de Dades (Relacional)

Un diagrama mental de l'estructura relacional implementada mitjançant les Migracions de Laravel.

```text
[ users ]
  ├── id (PK)
  ├── custom_id (String Únic)
  ├── name, surnames, email, password
  ├── role ('user', 'robot')
  ├── coins (Int)
  └── last_daily_reward (Timestamp)

[ xuxemons ] (Catàleg Mestre)
  ├── id (PK)
  ├── name, type, image
  └── base_stats...

[ items ] (Catàleg Mestre)
  ├── id (PK)
  ├── name, type ('xuxe', 'vacuna')
  └── is_stackable (Boolean)

[ user_xuxemons ] (Relació N:M - Instàncies Vives)
  ├── id (Pivot PK)
  ├── user_id (FK)
  ├── xuxemon_id (FK)
  ├── size ('Petit', 'Mitja', 'Gran')
  ├── food_eaten (Int)
  └── disease (String Nullable)

[ user_items ] (Relació N:M - Motxilla)
  ├── user_id (FK)
  ├── item_id (FK)
  └── quantity (Int)

[ friendships ] (Social)
  ├── user_id_1 (FK - Sol·licitant)
  ├── user_id_2 (FK - Receptor)
  └── status ('pending', 'accepted')

[ messages ] (Xat)
  ├── sender_id (FK)
  ├── receiver_id (FK)
  ├── content (Text)
  └── created_at (Timestamp)

[ settings ] (Configuració de Balanç del Joc)
  └── atracon_prob, sobredosis_prob, bajon_prob (Int)
```






## 7\.  Documentació de l'API REST

*(Un resum de les rutes principals que exposa l'arxiu `routes/api.php` de Laravel).*

| Mètode | Endpoint | Cos / Paràmetres | Descripció | Protecció |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/api/register` | `name`, `email`, `password`... | Crea el compte de l'usuari | Pública |
| `POST` | `/api/login` | `custom_id`, `password` | Autentica i retorna Token | Pública |
| `GET` | `/api/me` | - | Retorna el perfil i stats de l'usuari | Auth |
| `POST` | `/api/logout` | - | Revoca el token de seguretat | Auth |
| `GET` | `/api/xuxemons` | - | Llista la Xuxedex de l'usuari (Amb Pivot) | Auth |
| `POST` | `/api/xuxemons/{id}/feed` | `item_id` | Intenta evolucionar criatura consumint 1 item | Auth |
| `POST` | `/api/xuxemons/{id}/vaccinate`| `item_id` | Cura malalties de la criatura | Auth |
| `GET` | `/api/inventory` | - | Retorna els ítems que posseeix l'usuari | Auth |
| `GET` | `/api/friends/search` | `?q=Nom` | Cerca usuaris via Query | Auth |
| `POST` | `/api/friends/request` | `friend_id` | Envia sol·licitud amistat | Auth |
| `GET` | `/api/chat/{friendId}` | - | Recupera tot l'historial amb 1 amic | Auth |
| `POST` | `/api/chat/{friendId}` | `content` | Envia missatge nou | Auth |
| `POST` | `/api/battle/transfer` | `winner_id`, `loser_pivot`| Executa el robatori post-combat | Auth |
| `POST` | `/api/admin/give-item` | `user_id`, `item_id`, `qty`| L'admin regala un ítem al jugador | Auth + Rol 'robot' |
| `PATCH`| `/api/admin/settings` | Probabilitats (Int) | Modifica la dificultat/malalties globals | Auth + Rol 'robot' |








## 8\.  Guia d'Instal·lació i Desplegament (Docker)

L'entorn sencer està containeritzat per evitar el famós "A la meva màquina funciona". Tota l'arquitectura s'aixeca amb un sol comandament gràcies a Docker Compose.

### Requisits Previs

  * Docker & Docker Compose instal·lats.
  * Git.

### Passos d'Instal·lació

1.  **Clonar el repositori:**

    ```bash
    git clone <URL_DEL_TEU_REPO>
    cd xoxemons
    ```

2.  **Configuració del Backend (.env):**
    Copia l'arxiu d'exemple del backend.

    ```bash
    cd backend
    cp .env.example .env
    ```

    *(La configuració per defecte del `.env` ja hauria de coincidir amb els paràmetres del `docker-compose.yml` pel que fa a la connexió de MySQL).*

3.  **Aixecar la flota de contenidors:**
    Torna a l'arrel del projecte (on hi ha el `docker-compose.yml`) i executa:

    ```bash
    docker-compose up -d --build
    ```

    *Aquest procés compilarà la imatge d'Angular (creant el bundle de producció) i aixecarà PHP/Laravel i MySQL.*

4.  **Migracions i Llavors (Seeders) de BBDD:**
    Hem de crear l'estructura de la base de dades i omplir-la amb els Xuxemons i Items per defecte i l'usuari Administrador.
    Accedeix al contenidor del backend i executa els comandaments d'Artisan:

    ```bash
    docker-compose exec backend bash
    composer install
    php artisan key:generate
    php artisan migrate:fresh --seed
    exit
    ```

5.  **Accés a l'Aplicació:**

      * **Frontend:** Obre el teu navegador a `http://localhost:4200`
      * **Backend API:** S'exposa a `http://localhost:8000/api`







## 9\.  Seguretat i Protecció

Com a Arquitectes, la seguretat no és un afegit secundari, és la base:

  * **Passwords Encryptats:** Totes les contrasenyes es passen per funcions de *Hash* criptogràfiques (Bcrypt) al Laravel.
  * **Double Guards al Frontend:** L'accés a `/admin` està protegit per dos panys:
    1.  `authGuard`: Valida que tinguis el bitllet (token local).
    2.  `adminGuard`: Fa una crida asíncrona oculta al servidor per assegurar-se que el teu rol base de dades és realment `'robot'` i que no has manipulat el `localStorage`.
  * **Middlewares al Backend:** Encara que modifiquessis el frontend per accedir a botons d'Administrador, el Backend blinda aquestes rutes i et rebutjarà amb un error 403 Forbidden.
  * **Validació Reactiva Estricta:** Formulari controlat directament al Typescript (Reactive Forms) per evitar la injecció de variables estranyes o bypass de camps requerits des de l'Inspector de Codi (DevTools).







## 10\.  Roadmap i Futures Millores

Tot ecosistema pot continuar evolucionant. Aquestes són les línies de creixement futur plantejades per la v2.0 d'aquesta aplicació:

1.  **Migració de Xat a WebSockets:** Substituir el mecanisme de *Short Polling* actual per Laravel Reverb o Pusher per tenir latència 0 i estalviar trànsit i consultes SQL innecessàries.
2.  **Sistema d'Intercanvis (Trades):** Implementar una ruta per intercanviar pacíficament Xuxemons entre amics sense la violència del PVP. Requeriria un procés de bloqueig (lock) d'ítems mitjançant transaccions atòmiques de Base de dades.
3.  **Animations i Game Feel (CSS):** Millorar la representació visual de la batalla (barres de vida, daus 3D rodant) utilitzant llibreries d'animació d'Angular o CSS3 pur.
4.  **Caché Redis:** Afegir un servidor de Redis per emmagatzemar en memòria la llista de *Settings* o *Items*, ja que són elements mestres de només lectura que es consulten contínuament i no cal colpejar la base de dades MySQL a cada petició.






