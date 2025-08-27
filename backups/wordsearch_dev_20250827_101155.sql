--
-- PostgreSQL database dump
--

-- Dumped from database version 14.18 (Ubuntu 14.18-0ubuntu0.22.04.1)
-- Dumped by pg_dump version 14.18 (Ubuntu 14.18-0ubuntu0.22.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: games; Type: TABLE; Schema: public; Owner: wordsearch_dev_user
--

CREATE TABLE public.games (
    id integer NOT NULL,
    user_id integer,
    puzzle_id character varying(20) NOT NULL,
    theme character varying(50) NOT NULL,
    difficulty character varying(20) NOT NULL,
    start_time timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    end_time timestamp without time zone,
    elapsed_time integer DEFAULT 0,
    hints_used integer DEFAULT 0,
    words_found integer DEFAULT 0,
    words_found_data jsonb DEFAULT '[]'::jsonb,
    total_words integer NOT NULL,
    status character varying(20) DEFAULT 'active'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    grid_size integer DEFAULT 15 NOT NULL,
    puzzle_data jsonb,
    completion_time integer,
    completed_at timestamp without time zone
);


ALTER TABLE public.games OWNER TO wordsearch_dev_user;

--
-- Name: games_id_seq; Type: SEQUENCE; Schema: public; Owner: wordsearch_dev_user
--

CREATE SEQUENCE public.games_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.games_id_seq OWNER TO wordsearch_dev_user;

--
-- Name: games_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wordsearch_dev_user
--

ALTER SEQUENCE public.games_id_seq OWNED BY public.games.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: wordsearch_dev_user
--

CREATE TABLE public.users (
    id integer NOT NULL,
    username character varying(50) NOT NULL,
    email character varying(255) NOT NULL,
    first_name character varying(50) NOT NULL,
    last_name character varying(50) NOT NULL,
    password character varying(255) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    is_active boolean DEFAULT true,
    email_verified boolean DEFAULT false,
    reset_token character varying(255),
    reset_expires timestamp without time zone,
    default_theme character varying(50) DEFAULT 'animals'::character varying,
    default_level character varying(20) DEFAULT 'medium'::character varying,
    isadmin boolean DEFAULT false NOT NULL,
    default_diagonals boolean DEFAULT true,
    default_reverse boolean DEFAULT true
);


ALTER TABLE public.users OWNER TO wordsearch_dev_user;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: wordsearch_dev_user
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO wordsearch_dev_user;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wordsearch_dev_user
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: games id; Type: DEFAULT; Schema: public; Owner: wordsearch_dev_user
--

ALTER TABLE ONLY public.games ALTER COLUMN id SET DEFAULT nextval('public.games_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: wordsearch_dev_user
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: games; Type: TABLE DATA; Schema: public; Owner: wordsearch_dev_user
--

COPY public.games (id, user_id, puzzle_id, theme, difficulty, start_time, end_time, elapsed_time, hints_used, words_found, words_found_data, total_words, status, created_at, updated_at, grid_size, puzzle_data, completion_time, completed_at) FROM stdin;
1	1	p_68acd5858270c100	animals	easy	2025-08-25 21:28:37	\N	0	0	10	[]	10	completed	2025-08-25 14:28:37.596391	2025-08-25 14:28:37.596391	10	{"id": "p_68acd5858270c100", "grid": [["L", "I", "N", "M", "I", "D", "G", "E", "B", "F"], ["A", "D", "O", "K", "P", "W", "V", "V", "E", "R"], ["R", "Z", "E", "U", "T", "F", "W", "S", "E", "Y"], ["O", "G", "G", "G", "Z", "B", "O", "G", "Q", "R"], ["C", "G", "I", "H", "K", "O", "D", "B", "B", "E"], ["O", "E", "P", "X", "M", "A", "L", "E", "U", "T"], ["I", "W", "D", "L", "B", "L", "A", "E", "E", "S"], ["N", "R", "C", "H", "A", "W", "K", "T", "N", "B"], ["A", "T", "I", "B", "B", "A", "R", "L", "I", "O"], ["D", "I", "T", "Z", "N", "W", "I", "E", "X", "L"]], "seed": null, "size": 10, "words": ["MOOSE", "BEETLE", "HAWK", "LOBSTER", "DANIO", "CORAL", "PIGEON", "BADGER", "MIDGE", "RABBIT"], "options": {"size": 10, "reverse": true, "diagonals": true, "difficulty": "easy", "word_count": 10}, "failed_words": [], "placed_words": [{"end": [1, 8], "word": "MOOSE", "start": [5, 4], "reversed": false, "direction": [-1, 1], "placed_word": "MOOSE"}, {"end": [9, 7], "word": "BEETLE", "start": [4, 7], "reversed": false, "direction": [1, 0], "placed_word": "BEETLE"}, {"end": [7, 6], "word": "HAWK", "start": [7, 3], "reversed": false, "direction": [0, 1], "placed_word": "HAWK"}, {"end": [9, 9], "word": "LOBSTER", "start": [3, 9], "reversed": true, "direction": [1, 0], "placed_word": "RETSBOL"}, {"end": [9, 0], "word": "DANIO", "start": [5, 0], "reversed": true, "direction": [1, 0], "placed_word": "OINAD"}, {"end": [4, 0], "word": "CORAL", "start": [0, 0], "reversed": true, "direction": [1, 0], "placed_word": "LAROC"}, {"end": [5, 2], "word": "PIGEON", "start": [0, 2], "reversed": true, "direction": [1, 0], "placed_word": "NOEGIP"}, {"end": [1, 9], "word": "BADGER", "start": [6, 4], "reversed": false, "direction": [-1, 1], "placed_word": "BADGER"}, {"end": [0, 7], "word": "MIDGE", "start": [0, 3], "reversed": false, "direction": [0, 1], "placed_word": "MIDGE"}, {"end": [8, 6], "word": "RABBIT", "start": [8, 1], "reversed": true, "direction": [0, 1], "placed_word": "TIBBAR"}]}	3	2025-08-25 21:28:42
6	1	p_68acddafa08b5949	animals	easy	2025-08-25 22:03:27	\N	0	1	0	[]	10	completed	2025-08-25 15:03:27.719779	2025-08-25 15:03:27.719779	10	{"id": "p_68acddafa08b5949", "grid": [["P", "E", "A", "C", "O", "C", "K", "D", "C", "F"], ["L", "A", "C", "A", "P", "L", "A", "P", "J", "K"], ["K", "O", "A", "F", "O", "X", "E", "H", "R", "C"], ["R", "E", "A", "I", "C", "N", "X", "K", "R", "C"], ["R", "B", "Y", "C", "G", "S", "R", "A", "X", "C"], ["F", "Y", "L", "U", "H", "H", "T", "L", "A", "F"], ["L", "S", "I", "B", "I", "L", "N", "R", "N", "N"], ["L", "N", "T", "N", "A", "H", "P", "Z", "G", "J"], ["U", "V", "O", "H", "O", "R", "N", "E", "T", "V"], ["J", "G", "Z", "D", "X", "G", "B", "N", "K", "K"]], "seed": null, "size": 10, "words": ["PENGUIN", "BARB", "RHINO", "LOACH", "ALPACA", "HORNET", "PEACOCK", "FOX", "CARP", "RAT"], "options": {"size": 10, "reverse": true, "diagonals": true, "difficulty": "easy", "word_count": 10}, "failed_words": [], "placed_words": [{"end": [1, 7], "word": "PENGUIN", "start": [7, 1], "reversed": true, "direction": [-1, 1], "placed_word": "NIUGNEP"}, {"end": [9, 6], "word": "BARB", "start": [6, 3], "reversed": false, "direction": [1, 1], "placed_word": "BARB"}, {"end": [4, 6], "word": "RHINO", "start": [8, 2], "reversed": true, "direction": [-1, 1], "placed_word": "ONIHR"}, {"end": [5, 4], "word": "LOACH", "start": [1, 0], "reversed": false, "direction": [1, 1], "placed_word": "LOACH"}, {"end": [1, 6], "word": "ALPACA", "start": [1, 1], "reversed": true, "direction": [0, 1], "placed_word": "ACAPLA"}, {"end": [8, 8], "word": "HORNET", "start": [8, 3], "reversed": false, "direction": [0, 1], "placed_word": "HORNET"}, {"end": [0, 6], "word": "PEACOCK", "start": [0, 0], "reversed": false, "direction": [0, 1], "placed_word": "PEACOCK"}, {"end": [2, 5], "word": "FOX", "start": [2, 3], "reversed": false, "direction": [0, 1], "placed_word": "FOX"}, {"end": [4, 9], "word": "CARP", "start": [7, 6], "reversed": true, "direction": [-1, 1], "placed_word": "PRAC"}, {"end": [3, 8], "word": "RAT", "start": [5, 6], "reversed": true, "direction": [-1, 1], "placed_word": "TAR"}]}	42	2025-08-25 22:08:13
2	1	p_68acd593248da702	automotive	medium	2025-08-25 21:28:51	\N	0	0	15	[]	15	completed	2025-08-25 14:28:51.210998	2025-08-25 14:28:51.210998	15	{"id": "p_68acd593248da702", "grid": [["T", "U", "R", "N", " ", "S", "I", "G", "N", "A", "L", "W", "P", "L", "L"], ["B", "X", "S", "U", "T", "O", "L", "A", "I", "R", "I", "O", "P", "A", "B"], ["S", "H", "P", "I", "C", "K", "U", "P", "Q", "E", "B", "D", "O", "Y", "U"], ["V", "P", "E", "N", "O", "Z", " ", "E", "L", "P", "I", "R", "T", "O", "I"], ["A", "K", "M", "C", "K", "X", "F", "L", "G", "F", "O", "J", "L", "R", "C"], ["B", "E", "G", "N", "S", "I", "O", "I", "U", "P", "R", "J", "S", "I", "K"], ["G", "A", "M", "V", "X", "S", "Z", "P", "R", "X", "P", "Y", "C", "N", "H"], ["Z", "V", "S", "D", "J", "W", "R", "O", "G", "E", "G", "E", "X", "U", "Y"], ["U", "Z", "A", "S", "T", "A", "V", "A", "H", "P", "S", "E", "C", "Q", "Z"], ["J", "Q", "G", "G", "N", "E", "F", "G", "N", "O", "D", "T", "N", "M", "B"], ["Q", "T", "D", "V", "L", "R", "A", "C", " ", "S", "T", "R", "O", "P", "S"], ["Z", "M", "D", "A", "J", "Q", "X", "H", "F", "R", "Z", "I", "W", "N", "U"], ["K", "G", "V", "D", "P", "T", "R", "K", "A", "G", "S", "J", "D", "D", "E"], ["O", "A", "M", "Z", "F", "H", "D", "M", "C", "U", "N", "L", "Z", "N", "C"], ["H", "C", "B", "G", "N", "E", "P", "X", "E", "D", "O", "N", "Z", "B", "A"]], "seed": null, "size": 15, "words": ["LUXGEN", "UNIROYAL", "GAZ", "RAM", "DONGFENG", "FIRESTONE", "TURN SIGNAL", "HAVAL", "PICKUP", "TRIPLE ZONE", "XPENG", "LOTUS", "BUICK", "ZASTAVA", "SPORTS CAR"], "options": {"size": 15, "reverse": true, "diagonals": true, "difficulty": "medium", "word_count": 15}, "failed_words": [], "placed_words": [{"end": [9, 12], "word": "LUXGEN", "start": [4, 7], "reversed": false, "direction": [1, 1], "placed_word": "LUXGEN"}, {"end": [7, 13], "word": "UNIROYAL", "start": [0, 13], "reversed": true, "direction": [1, 0], "placed_word": "LAYORINU"}, {"end": [5, 2], "word": "GAZ", "start": [7, 0], "reversed": true, "direction": [-1, 1], "placed_word": "ZAG"}, {"end": [11, 9], "word": "RAM", "start": [13, 7], "reversed": true, "direction": [-1, 1], "placed_word": "MAR"}, {"end": [9, 10], "word": "DONGFENG", "start": [9, 3], "reversed": true, "direction": [0, 1], "placed_word": "GNEFGNOD"}, {"end": [12, 14], "word": "FIRESTONE", "start": [4, 6], "reversed": false, "direction": [1, 1], "placed_word": "FIRESTONE"}, {"end": [0, 10], "word": "TURN SIGNAL", "start": [0, 0], "reversed": false, "direction": [0, 1], "placed_word": "TURN SIGNAL"}, {"end": [10, 4], "word": "HAVAL", "start": [14, 0], "reversed": false, "direction": [-1, 1], "placed_word": "HAVAL"}, {"end": [2, 7], "word": "PICKUP", "start": [2, 2], "reversed": false, "direction": [0, 1], "placed_word": "PICKUP"}, {"end": [3, 12], "word": "TRIPLE ZONE", "start": [3, 2], "reversed": true, "direction": [0, 1], "placed_word": "ENOZ ELPIRT"}, {"end": [14, 7], "word": "XPENG", "start": [14, 3], "reversed": true, "direction": [0, 1], "placed_word": "GNEPX"}, {"end": [1, 6], "word": "LOTUS", "start": [1, 2], "reversed": true, "direction": [0, 1], "placed_word": "SUTOL"}, {"end": [5, 14], "word": "BUICK", "start": [1, 14], "reversed": false, "direction": [1, 0], "placed_word": "BUICK"}, {"end": [8, 7], "word": "ZASTAVA", "start": [8, 1], "reversed": false, "direction": [0, 1], "placed_word": "ZASTAVA"}, {"end": [10, 14], "word": "SPORTS CAR", "start": [10, 5], "reversed": true, "direction": [0, 1], "placed_word": "RAC STROPS"}]}	3	2025-08-25 21:28:55
4	1	p_68acd5c62aa71444	animals	medium	2025-08-25 21:29:42	\N	0	0	2	["SEA LION", "WILDEBEEST"]	15	active	2025-08-25 14:29:42.235583	2025-08-25 14:29:42.235583	15	{"id": "p_68acd5c62aa71444", "grid": [["W", "J", "M", "W", "I", "L", "D", "E", "B", "E", "E", "S", "T", "S", "H"], ["J", "F", "T", "B", "J", "I", "A", "L", "B", "O", "K", "B", "O", "A", "E"], ["I", "M", "F", "P", "Y", "J", "J", "Y", "K", "S", "U", "K", "E", "O", "T"], ["X", "D", "X", "F", "T", "A", "C", "Y", "M", "C", "T", "M", "C", "Z", "O"], ["T", "I", "M", "P", "A", "L", "A", "Y", "R", "A", "L", "E", "O", "S", "P"], ["Z", "W", "F", "B", "S", "Z", "P", "C", "A", "R", "A", "K", "E", "P", "D"], ["K", "E", "Y", "A", "A", "T", "Y", "O", "G", "S", "P", "C", "N", "I", "F"], ["S", "A", "D", "R", "N", "P", "B", "W", "M", "O", "L", "L", "Y", "D", "L"], ["E", "S", "S", "N", "A", "G", "A", "S", "K", "U", "N", "K", "X", "E", "K"], ["A", "E", "C", "A", "C", "M", "R", "J", "W", "O", "O", "D", "P", "R", "E"], [" ", "L", "B", "C", "O", "F", "A", "P", "G", "N", "H", "M", "A", "C", "R"], ["L", "I", "S", "L", "N", "S", "K", "G", "C", "A", "W", "R", "L", "X", "H"], ["I", "X", "Q", "E", "D", "C", "E", "N", "T", "I", "P", "E", "D", "E", "B"], ["O", "P", "L", "I", "A", "V", "J", "H", "O", "R", "S", "E", "Y", "W", "W"], ["N", "R", "B", "H", "F", "Q", "P", "F", "A", "L", "B", "T", "W", "E", "T"]], "seed": null, "size": 15, "words": ["SKUNK", "HORSE", "SPIDER", "WILDEBEEST", "CENTIPEDE", "SEA LION", "CAPYBARA", "COW", "IMPALA", "MOLLY", "BOA", "ANACONDA", "OSCAR", "WEASEL", "BARNACLE"], "options": {"size": 15, "reverse": false, "diagonals": false, "word_count": 15}, "failed_words": [], "placed_words": [{"end": [8, 11], "word": "SKUNK", "start": [8, 7], "reversed": false, "direction": [0, 1], "placed_word": "SKUNK"}, {"end": [13, 11], "word": "HORSE", "start": [13, 7], "reversed": false, "direction": [0, 1], "placed_word": "HORSE"}, {"end": [9, 13], "word": "SPIDER", "start": [4, 13], "reversed": false, "direction": [1, 0], "placed_word": "SPIDER"}, {"end": [0, 12], "word": "WILDEBEEST", "start": [0, 3], "reversed": false, "direction": [0, 1], "placed_word": "WILDEBEEST"}, {"end": [12, 13], "word": "CENTIPEDE", "start": [12, 5], "reversed": false, "direction": [0, 1], "placed_word": "CENTIPEDE"}, {"end": [14, 0], "word": "SEA LION", "start": [7, 0], "reversed": false, "direction": [1, 0], "placed_word": "SEA LION"}, {"end": [10, 6], "word": "CAPYBARA", "start": [3, 6], "reversed": false, "direction": [1, 0], "placed_word": "CAPYBARA"}, {"end": [7, 7], "word": "COW", "start": [5, 7], "reversed": false, "direction": [1, 0], "placed_word": "COW"}, {"end": [4, 6], "word": "IMPALA", "start": [4, 1], "reversed": false, "direction": [0, 1], "placed_word": "IMPALA"}, {"end": [7, 12], "word": "MOLLY", "start": [7, 8], "reversed": false, "direction": [0, 1], "placed_word": "MOLLY"}, {"end": [1, 13], "word": "BOA", "start": [1, 11], "reversed": false, "direction": [0, 1], "placed_word": "BOA"}, {"end": [13, 4], "word": "ANACONDA", "start": [6, 4], "reversed": false, "direction": [1, 0], "placed_word": "ANACONDA"}, {"end": [5, 9], "word": "OSCAR", "start": [1, 9], "reversed": false, "direction": [1, 0], "placed_word": "OSCAR"}, {"end": [10, 1], "word": "WEASEL", "start": [5, 1], "reversed": false, "direction": [1, 0], "placed_word": "WEASEL"}, {"end": [12, 3], "word": "BARNACLE", "start": [5, 3], "reversed": false, "direction": [1, 0], "placed_word": "BARNACLE"}]}	\N	\N
7	\N	p_68addd711736f826	animals	hard	2025-08-26 16:14:41	\N	0	0	0	[]	20	active	2025-08-26 09:14:41.156955	2025-08-26 09:14:41.156955	20	{"id": "p_68addd711736f826", "grid": [["U", "W", "Y", "L", "C", "G", "P", "T", "U", "R", "K", "E", "Y", "I", "R", "J", "N", "F", "Q", "X"], ["I", "A", "U", "R", "B", "C", "R", "A", "X", "V", "D", "C", "S", "F", "L", "H", "U", "D", "R", "J"], ["S", "E", "S", "J", "M", "C", "Z", "A", "N", "X", "V", "Q", "D", "C", "I", "F", "A", "I", "K", "B"], ["I", "R", "K", "V", "R", "V", "Y", "F", "S", "T", "P", "W", "U", "Q", "A", "X", "L", "T", "I", "H"], ["I", "C", "U", "P", "K", "Y", "W", "F", "T", "S", "H", "E", "W", "U", "M", "L", "W", "O", "S", "K"], ["M", "Y", "N", "I", "D", "P", "N", "E", "I", "T", "H", "E", "S", "Y", "P", "E", "L", "I", "C", "T"], ["H", "H", "K", "Y", "Z", "O", "E", "M", "L", "R", "F", "O", "R", "E", "N", "E", "F", "O", "P", "B"], ["W", "L", "V", "A", "C", "B", "H", "O", "G", "W", "E", "N", "P", "E", "K", "R", "N", "A", "P", "A"], ["Q", "C", "V", "L", "T", "C", "J", "J", "W", "D", "B", "F", "C", "P", "A", "R", "T", "T", "W", "S"], ["L", "Z", "A", "B", "I", "X", "A", "T", "I", "X", "O", "N", "L", "T", "E", "R", "N", "D", "M", "S"], ["M", "F", "Y", "R", "M", "L", "R", "D", "A", "G", "B", "A", "S", "Y", "H", "R", "Z", "J", "U", "E"], ["K", "I", "T", "G", "J", "S", "Y", "H", "N", "N", "G", "S", "T", "P", "H", "A", "R", "E", "H", "S"], ["Q", "S", "L", "K", "X", "T", "N", "U", "T", "C", "S", "I", "O", "R", "D", "P", "X", "R", "X", "R"], ["O", "B", "G", "L", "A", "I", "B", "O", "E", "S", "Z", "A", "R", "P", "Z", "L", "J", "Q", "D", "D"], ["S", "I", "G", "K", "I", "D", "B", "V", "A", "L", "P", "A", "C", "A", "O", "R", "B", "E", "A", "R"], ["Y", "E", "Y", "R", "H", "P", "B", "J", "T", "P", "I", "H", "Y", "N", "F", "S", "G", "P", "Z", "W"], ["W", "F", "U", "W", "J", "F", "E", "R", "E", "P", "X", "C", "K", "F", "X", "F", "S", "I", "F", "K"], ["W", "V", "W", "J", "V", "H", "G", "D", "R", "K", "B", "H", "C", "C", "K", "B", "E", "U", "E", "R"], ["X", "P", "O", "E", "G", "B", "R", "S", "E", "I", "S", "E", "V", "F", "O", "S", "Y", "T", "M", "E"], ["L", "D", "X", "D", "H", "U", "Z", "N", "R", "F", "D", "G", "B", "M", "L", "M", "H", "K", "F", "L"]], "seed": null, "size": 20, "words": ["NEWT", "SCALLOP", "SKUNK", "ANTEATER", "HARE", "PANTHER", "FIREFLY", "STARFISH", "BASS", "OSTRICH", "OPOSSUM", "TURKEY", "GIRAFFE", "KATYDID", "BEAR", "MILLIPEDE", "GRASSHOPPER", "ALPACA", "FALCON", "BEE"], "options": {"size": 20, "reverse": false, "diagonals": true, "difficulty": "hard", "word_count": 20}, "failed_words": [], "placed_words": [{"end": [3, 17], "word": "NEWT", "start": [6, 14], "reversed": false, "direction": [-1, 1], "placed_word": "NEWT"}, {"end": [7, 18], "word": "SCALLOP", "start": [1, 12], "reversed": false, "direction": [1, 1], "placed_word": "SCALLOP"}, {"end": [6, 2], "word": "SKUNK", "start": [2, 2], "reversed": false, "direction": [1, 0], "placed_word": "SKUNK"}, {"end": [17, 8], "word": "ANTEATER", "start": [10, 8], "reversed": false, "direction": [1, 0], "placed_word": "ANTEATER"}, {"end": [11, 17], "word": "HARE", "start": [11, 14], "reversed": false, "direction": [0, 1], "placed_word": "HARE"}, {"end": [6, 12], "word": "PANTHER", "start": [0, 6], "reversed": false, "direction": [1, 1], "placed_word": "PANTHER"}, {"end": [10, 13], "word": "FIREFLY", "start": [4, 7], "reversed": false, "direction": [1, 1], "placed_word": "FIREFLY"}, {"end": [3, 19], "word": "STARFISH", "start": [10, 12], "reversed": false, "direction": [-1, 1], "placed_word": "STARFISH"}, {"end": [9, 19], "word": "BASS", "start": [6, 19], "reversed": false, "direction": [1, 0], "placed_word": "BASS"}, {"end": [7, 6], "word": "OSTRICH", "start": [13, 0], "reversed": false, "direction": [-1, 1], "placed_word": "OSTRICH"}, {"end": [18, 18], "word": "OPOSSUM", "start": [12, 12], "reversed": false, "direction": [1, 1], "placed_word": "OPOSSUM"}, {"end": [0, 12], "word": "TURKEY", "start": [0, 7], "reversed": false, "direction": [0, 1], "placed_word": "TURKEY"}, {"end": [17, 16], "word": "GIRAFFE", "start": [11, 10], "reversed": false, "direction": [1, 1], "placed_word": "GIRAFFE"}, {"end": [8, 9], "word": "KATYDID", "start": [14, 3], "reversed": false, "direction": [-1, 1], "placed_word": "KATYDID"}, {"end": [14, 19], "word": "BEAR", "start": [14, 16], "reversed": false, "direction": [0, 1], "placed_word": "BEAR"}, {"end": [18, 8], "word": "MILLIPEDE", "start": [10, 0], "reversed": false, "direction": [1, 1], "placed_word": "MILLIPEDE"}, {"end": [10, 15], "word": "GRASSHOPPER", "start": [0, 5], "reversed": false, "direction": [1, 1], "placed_word": "GRASSHOPPER"}, {"end": [14, 13], "word": "ALPACA", "start": [14, 8], "reversed": false, "direction": [0, 1], "placed_word": "ALPACA"}, {"end": [5, 6], "word": "FALCON", "start": [10, 1], "reversed": false, "direction": [-1, 1], "placed_word": "FALCON"}, {"end": [5, 7], "word": "BEE", "start": [7, 5], "reversed": false, "direction": [-1, 1], "placed_word": "BEE"}]}	\N	\N
3	1	p_68acd5a2e9360930	food	hard	2025-08-25 21:29:06	\N	0	0	19	[]	20	completed	2025-08-25 14:29:07.01729	2025-08-25 14:29:07.01729	20	{"id": "p_68acd5a2e9360930", "grid": [["C", "U", "H", "Q", "V", "I", "B", "A", "J", "I", "B", "M", "A", "O", "J", "R", "C", "N", "Q", "H"], ["A", "W", "L", "X", "W", "D", "G", "U", "X", "U", "P", "O", "O", "U", "J", "L", "M", "B", "Z", "U"], ["T", "P", "D", "V", "C", "L", "A", "M", "Y", "R", "Y", "N", "A", "T", "I", "B", "B", "A", "R", "R"], ["F", "T", "N", "Q", "Q", "Q", "B", "O", "T", "P", "F", "Y", "V", "D", "W", "X", "H", "I", "X", "E"], ["I", "I", "C", "E", "R", "U", "T", "J", "L", "Y", "A", "D", "G", "H", "A", "A", "C", "F", "R", "W"], ["S", "C", "A", "G", "R", "Y", "G", "Y", "C", "Z", "O", "E", "I", "W", "T", "O", "R", "M", "K", "O"], ["H", "D", "O", "I", "M", "X", "B", "R", "X", "M", "R", "K", "Z", "A", "D", "A", "N", "V", "P", "L"], ["X", "T", "W", "Q", "B", "S", "E", "P", "Y", "J", "N", "H", "I", "U", "P", "V", "P", "D", "E", "F"], ["M", "C", "U", "H", "S", "A", "I", "U", "O", "J", "U", "L", "B", "L", "C", "N", "Q", "S", "E", "I"], ["B", "A", "S", "S", "M", "P", "Z", "Q", "C", "T", "U", "X", "K", "K", "Z", "O", "A", "L", "P", "L"], ["X", "S", "H", "H", "R", "S", "F", "B", "Z", "E", "A", "M", "R", "O", "W", "N", "C", "E", "L", "U"], ["Y", "K", "L", "X", "H", "I", "X", "I", "B", "L", "D", "T", "O", "B", "H", "N", "S", "V", "F", "A"], ["G", "T", "I", "A", "L", "Y", "M", "N", "V", "K", "H", "O", "O", "R", "I", "B", "A", "Z", "M", "C"], ["C", "U", "E", "N", "U", "K", "U", "F", "O", "Q", "T", "R", "B", "O", "T", "E", "L", "S", "A", "T"], ["N", "H", "S", "T", "M", "F", "U", "S", "K", "R", "R", "G", "N", "C", "I", "O", "I", "A", "C", "O"], ["Q", "W", "I", "T", "K", "E", "X", "N", "P", "A", "W", "C", "L", "C", "N", "Q", "F", "R", "K", "H"], ["A", "U", "J", "L", "B", "M", "D", "B", "F", "P", "E", "I", "G", "O", "G", "M", "U", "D", "E", "R"], ["N", "B", "A", "K", "I", "J", "Y", "A", "C", "L", "U", "Y", "V", "L", "L", "K", "P", "I", "R", "F"], ["H", "Q", "D", "I", "N", "C", "J", "E", "I", "V", "Z", "G", "R", "I", "N", "L", "V", "N", "E", "Q"], ["K", "O", "R", "F", "L", "L", "V", "P", "M", "F", "C", "I", "W", "C", "K", "U", "K", "E", "L", "B"]], "seed": null, "size": 20, "words": ["BASS", "MACKEREL", "RABBIT", "KINMEDAI", "HATA", "WHITING", "CATFISH", "QUAIL", "CAULIFLOWER", "POTATO", "FARRO", "AJI", "CHILI", "COD", "CREAM", "BROCCOLI", "CLAM", "SARDINE", "ELK", "BURI"], "options": {"size": 20, "reverse": true, "diagonals": true, "difficulty": "hard", "word_count": 20}, "failed_words": [], "placed_words": [{"end": [9, 3], "word": "BASS", "start": [9, 0], "reversed": false, "direction": [0, 1], "placed_word": "BASS"}, {"end": [19, 18], "word": "MACKEREL", "start": [12, 18], "reversed": false, "direction": [1, 0], "placed_word": "MACKEREL"}, {"end": [2, 18], "word": "RABBIT", "start": [2, 13], "reversed": true, "direction": [0, 1], "placed_word": "TIBBAR"}, {"end": [18, 8], "word": "KINMEDAI", "start": [11, 1], "reversed": false, "direction": [1, 1], "placed_word": "KINMEDAI"}, {"end": [3, 16], "word": "HATA", "start": [6, 13], "reversed": true, "direction": [-1, 1], "placed_word": "ATAH"}, {"end": [16, 14], "word": "WHITING", "start": [10, 14], "reversed": false, "direction": [1, 0], "placed_word": "WHITING"}, {"end": [6, 0], "word": "CATFISH", "start": [0, 0], "reversed": false, "direction": [1, 0], "placed_word": "CATFISH"}, {"end": [19, 4], "word": "QUAIL", "start": [15, 0], "reversed": false, "direction": [1, 1], "placed_word": "QUAIL"}, {"end": [12, 19], "word": "CAULIFLOWER", "start": [2, 19], "reversed": true, "direction": [1, 0], "placed_word": "REWOLFILUAC"}, {"end": [12, 12], "word": "POTATO", "start": [7, 7], "reversed": false, "direction": [1, 1], "placed_word": "POTATO"}, {"end": [12, 12], "word": "FARRO", "start": [16, 8], "reversed": false, "direction": [-1, 1], "placed_word": "FARRO"}, {"end": [0, 9], "word": "AJI", "start": [0, 7], "reversed": false, "direction": [0, 1], "placed_word": "AJI"}, {"end": [17, 4], "word": "CHILI", "start": [13, 0], "reversed": false, "direction": [1, 1], "placed_word": "CHILI"}, {"end": [4, 16], "word": "COD", "start": [6, 14], "reversed": true, "direction": [-1, 1], "placed_word": "DOC"}, {"end": [5, 8], "word": "CREAM", "start": [9, 4], "reversed": true, "direction": [-1, 1], "placed_word": "MAERC"}, {"end": [18, 13], "word": "BROCCOLI", "start": [11, 13], "reversed": false, "direction": [1, 0], "placed_word": "BROCCOLI"}, {"end": [2, 7], "word": "CLAM", "start": [2, 4], "reversed": false, "direction": [0, 1], "placed_word": "CLAM"}, {"end": [19, 17], "word": "SARDINE", "start": [13, 17], "reversed": false, "direction": [1, 0], "placed_word": "SARDINE"}, {"end": [12, 9], "word": "ELK", "start": [10, 9], "reversed": false, "direction": [1, 0], "placed_word": "ELK"}, {"end": [3, 6], "word": "BURI", "start": [6, 3], "reversed": true, "direction": [-1, 1], "placed_word": "IRUB"}]}	3	2025-08-25 21:29:11
5	\N	p_68acdcee11cb9232	animals	easy	2025-08-25 22:00:14	\N	0	0	0	[]	10	active	2025-08-25 15:00:14.134951	2025-08-25 15:00:14.134951	10	{"id": "p_68acdcee11cb9232", "grid": [["L", "I", "M", "P", "A", "L", "A", "E", "M", "P"], ["P", "H", "I", "P", "P", "O", "G", "V", "M", "C"], ["G", "A", "Z", "E", "L", "L", "E", "E", "E", "F"], ["V", "P", "T", "M", "N", "T", "U", "K", "W", "S"], ["Y", "M", "I", "D", "G", "E", "O", "W", "B", "P"], ["K", "N", "E", "G", "L", "Y", "O", "B", "E", "I"], ["S", "N", "A", "K", "E", "Z", "T", "C", "T", "D"], ["M", "A", "N", "T", "I", "S", "I", "E", "T", "E"], ["M", "K", "J", "S", "P", "L", "M", "E", "A", "R"], ["I", "Z", "N", "P", "A", "R", "R", "O", "T", "H"]], "seed": null, "size": 10, "words": ["MIDGE", "GAZELLE", "SPIDER", "LICE", "PARROT", "MANTIS", "BETTA", "HIPPO", "SNAKE", "IMPALA"], "options": {"size": 10, "reverse": false, "diagonals": true, "difficulty": "easy", "word_count": 10}, "failed_words": [], "placed_words": [{"end": [4, 5], "word": "MIDGE", "start": [4, 1], "reversed": false, "direction": [0, 1], "placed_word": "MIDGE"}, {"end": [2, 6], "word": "GAZELLE", "start": [2, 0], "reversed": false, "direction": [0, 1], "placed_word": "GAZELLE"}, {"end": [8, 9], "word": "SPIDER", "start": [3, 9], "reversed": false, "direction": [1, 0], "placed_word": "SPIDER"}, {"end": [5, 8], "word": "LICE", "start": [8, 5], "reversed": false, "direction": [-1, 1], "placed_word": "LICE"}, {"end": [9, 8], "word": "PARROT", "start": [9, 3], "reversed": false, "direction": [0, 1], "placed_word": "PARROT"}, {"end": [7, 5], "word": "MANTIS", "start": [7, 0], "reversed": false, "direction": [0, 1], "placed_word": "MANTIS"}, {"end": [8, 8], "word": "BETTA", "start": [4, 8], "reversed": false, "direction": [1, 0], "placed_word": "BETTA"}, {"end": [1, 5], "word": "HIPPO", "start": [1, 1], "reversed": false, "direction": [0, 1], "placed_word": "HIPPO"}, {"end": [6, 4], "word": "SNAKE", "start": [6, 0], "reversed": false, "direction": [0, 1], "placed_word": "SNAKE"}, {"end": [0, 6], "word": "IMPALA", "start": [0, 1], "reversed": false, "direction": [0, 1], "placed_word": "IMPALA"}]}	\N	\N
8	\N	p_68addd956accc554	animals	expert	2025-08-26 16:15:17	\N	0	0	0	[]	35	active	2025-08-26 09:15:17.499036	2025-08-26 09:15:17.499036	25	{"id": "p_68addd956accc554", "grid": [["W", "H", "V", "I", "I", "I", "S", "R", "T", "I", "Y", "W", "P", "S", "U", "E", "D", "C", "X", "M", "A", "R", "I", "N", "O"], ["E", "Y", "Z", "C", "L", "G", "W", "T", "H", "L", "K", "K", "Z", "E", "L", "K", "I", "Q", "S", "E", "P", "U", "X", "E", "F"], ["U", "V", "X", "R", "O", "J", "G", "T", "A", "J", "N", "P", "F", "T", "C", "W", "A", "P", "B", "B", "Y", "U", "S", "U", "D"], ["W", "H", "M", "G", "B", "G", "S", "O", "G", "R", "E", "E", "E", "U", "T", "L", "E", "O", "P", "A", "R", "D", "S", "T", "B"], ["S", "W", "Q", "O", "S", "J", "Z", "V", "L", "X", "F", "E", "D", "I", "X", "I", "A", "B", "M", "R", "O", "U", "R", "C", "R"], ["O", "K", "A", "N", "T", "G", "K", "S", "R", "D", "B", "I", "N", "L", "K", "U", "C", "V", "K", "R", "Q", "D", "S", "G", "J"], ["Z", "A", "U", "Y", "E", "B", "P", "V", "E", "N", "F", "I", "S", "N", "N", "Y", "C", "K", "Z", "H", "O", "R", "S", "E", "B"], ["J", "B", "N", "N", "R", "B", "X", "B", "J", "A", "B", "I", "P", "H", "U", "U", "J", "Y", "N", "I", "N", "L", "M", "X", "E"], ["L", "F", "A", "B", "K", "H", "X", "G", "W", "O", "L", "C", "S", "P", "P", "H", "A", "W", "K", "J", "L", "H", "A", "V", "G"], ["E", "I", "R", "R", "X", "S", "D", "A", "R", "I", "X", "X", "O", "H", "L", "C", "V", "E", "U", "O", "L", "K", "R", "J", "I"], ["K", "F", "A", "B", "K", "J", "O", "S", "T", "R", "I", "C", "H", "Z", "G", "W", "V", "T", "R", "E", "U", "K", "M", "L", "R"], ["A", "L", "B", "A", "T", "R", "O", "S", "S", "S", "Q", "Z", "Q", "U", "A", "I", "L", "K", "P", "C", "S", "U", "O", "H", "A"], ["M", "N", "C", "V", "F", "D", "K", "Q", "H", "C", "U", "V", "M", "Q", "Q", "D", "B", "M", "Z", "H", "C", "W", "T", "F", "F"], ["C", "W", "Q", "B", "D", "G", "R", "S", "B", "P", "T", "Q", "B", "U", "S", "I", "P", "I", "K", "U", "A", "P", "P", "G", "F"], ["A", "V", "J", "G", "F", "E", "I", "U", "E", "T", "T", "I", "G", "E", "R", "S", "E", "Y", "B", "L", "L", "M", "A", "W", "E"], ["P", "R", "B", "G", "V", "F", "V", "M", "E", "A", "I", "T", "Z", "W", "O", "C", "E", "M", "U", "S", "L", "G", "O", "D", "A"], ["P", "H", "E", "A", "S", "A", "N", "T", "P", "R", "Z", "D", "D", "X", "S", "U", "Q", "I", "R", "B", "O", "G", "O", "R", "N"], ["W", "V", "E", "K", "P", "O", "S", "E", "R", "M", "N", "M", "V", "C", "T", "S", "N", "V", "N", "I", "P", "W", "R", "Z", "T"], ["P", "B", "B", "E", "O", "Q", "D", "J", "J", "I", "W", "X", "G", "E", "I", "W", "N", "I", "K", "U", "R", "D", "T", "V", "E"], ["S", "Z", "S", "C", "N", "B", "E", "G", "I", "G", "P", "A", "F", "F", "N", "K", "U", "P", "M", "G", "K", "A", "G", "B", "A"], ["H", "T", "C", "H", "I", "P", "P", "O", "U", "A", "M", "C", "R", "V", "G", "O", "O", "E", "R", "S", "L", "I", "Q", "I", "T"], ["T", "A", "P", "I", "R", "Q", "E", "T", "Y", "N", "W", "Z", "A", "H", "R", "S", "F", "R", "G", "E", "C", "K", "O", "I", "E"], ["R", "G", "W", "W", "R", "Y", "K", "F", "G", "R", "O", "U", "S", "E", "A", "Z", "N", "S", "S", "X", "X", "Q", "G", "O", "R"], ["Y", "L", "H", "G", "M", "F", "J", "P", "M", "U", "F", "S", "A", "W", "Y", "Q", "O", "U", "X", "C", "O", "U", "G", "A", "R"], ["Y", "N", "P", "U", "Y", "H", "K", "A", "N", "B", "M", "B", "N", "F", "V", "J", "J", "H", "A", "H", "P", "B", "R", "C", "P"]], "seed": null, "size": 25, "words": ["VIPER", "RACCOON", "BEAVER", "HORSE", "ANTEATER", "ALBATROSS", "TIGER", "EMU", "HIPPO", "DISCUS", "MARMOT", "TICK", "GROUSE", "ROBIN", "LEOPARD", "DUCK", "SKUNK", "PHEASANT", "LOBSTER", "GECKO", "GOLDFISH", "STINGRAY", "PTARMIGAN", "QUAIL", "BEETLE", "HAWK", "COUGAR", "GIRAFFE", "SEAL", "SCALLOP", "FISH", "BEE", "OSTRICH", "STARFISH", "TAPIR"], "options": {"size": 25, "reverse": false, "diagonals": true, "difficulty": "expert", "word_count": 35}, "failed_words": [], "placed_words": [{"end": [21, 17], "word": "VIPER", "start": [17, 17], "reversed": false, "direction": [1, 0], "placed_word": "VIPER"}, {"end": [16, 6], "word": "RACCOON", "start": [22, 0], "reversed": false, "direction": [-1, 1], "placed_word": "RACCOON"}, {"end": [13, 6], "word": "BEAVER", "start": [18, 1], "reversed": false, "direction": [-1, 1], "placed_word": "BEAVER"}, {"end": [6, 23], "word": "HORSE", "start": [6, 19], "reversed": false, "direction": [0, 1], "placed_word": "HORSE"}, {"end": [22, 24], "word": "ANTEATER", "start": [15, 24], "reversed": false, "direction": [1, 0], "placed_word": "ANTEATER"}, {"end": [11, 8], "word": "ALBATROSS", "start": [11, 0], "reversed": false, "direction": [0, 1], "placed_word": "ALBATROSS"}, {"end": [14, 14], "word": "TIGER", "start": [14, 10], "reversed": false, "direction": [0, 1], "placed_word": "TIGER"}, {"end": [15, 18], "word": "EMU", "start": [15, 16], "reversed": false, "direction": [0, 1], "placed_word": "EMU"}, {"end": [20, 7], "word": "HIPPO", "start": [20, 3], "reversed": false, "direction": [0, 1], "placed_word": "HIPPO"}, {"end": [17, 15], "word": "DISCUS", "start": [12, 15], "reversed": false, "direction": [1, 0], "placed_word": "DISCUS"}, {"end": [12, 22], "word": "MARMOT", "start": [7, 22], "reversed": false, "direction": [1, 0], "placed_word": "MARMOT"}, {"end": [6, 17], "word": "TICK", "start": [3, 14], "reversed": false, "direction": [1, 1], "placed_word": "TICK"}, {"end": [22, 13], "word": "GROUSE", "start": [22, 8], "reversed": false, "direction": [0, 1], "placed_word": "GROUSE"}, {"end": [5, 12], "word": "ROBIN", "start": [9, 8], "reversed": false, "direction": [-1, 1], "placed_word": "ROBIN"}, {"end": [3, 21], "word": "LEOPARD", "start": [3, 15], "reversed": false, "direction": [0, 1], "placed_word": "LEOPARD"}, {"end": [1, 15], "word": "DUCK", "start": [4, 12], "reversed": false, "direction": [-1, 1], "placed_word": "DUCK"}, {"end": [8, 4], "word": "SKUNK", "start": [4, 0], "reversed": false, "direction": [1, 1], "placed_word": "SKUNK"}, {"end": [16, 7], "word": "PHEASANT", "start": [16, 0], "reversed": false, "direction": [0, 1], "placed_word": "PHEASANT"}, {"end": [7, 4], "word": "LOBSTER", "start": [1, 4], "reversed": false, "direction": [1, 0], "placed_word": "LOBSTER"}, {"end": [21, 22], "word": "GECKO", "start": [21, 18], "reversed": false, "direction": [0, 1], "placed_word": "GECKO"}, {"end": [9, 13], "word": "GOLDFISH", "start": [2, 6], "reversed": false, "direction": [1, 1], "placed_word": "GOLDFISH"}, {"end": [23, 14], "word": "STINGRAY", "start": [16, 14], "reversed": false, "direction": [1, 0], "placed_word": "STINGRAY"}, {"end": [21, 9], "word": "PTARMIGAN", "start": [13, 9], "reversed": false, "direction": [1, 0], "placed_word": "PTARMIGAN"}, {"end": [11, 16], "word": "QUAIL", "start": [11, 12], "reversed": false, "direction": [0, 1], "placed_word": "QUAIL"}, {"end": [0, 15], "word": "BEETLE", "start": [5, 10], "reversed": false, "direction": [-1, 1], "placed_word": "BEETLE"}, {"end": [8, 18], "word": "HAWK", "start": [8, 15], "reversed": false, "direction": [0, 1], "placed_word": "HAWK"}, {"end": [23, 24], "word": "COUGAR", "start": [23, 19], "reversed": false, "direction": [0, 1], "placed_word": "COUGAR"}, {"end": [14, 24], "word": "GIRAFFE", "start": [8, 24], "reversed": false, "direction": [1, 0], "placed_word": "GIRAFFE"}, {"end": [8, 10], "word": "SEAL", "start": [5, 7], "reversed": false, "direction": [1, 1], "placed_word": "SEAL"}, {"end": [17, 20], "word": "SCALLOP", "start": [11, 20], "reversed": false, "direction": [1, 0], "placed_word": "SCALLOP"}, {"end": [12, 8], "word": "FISH", "start": [15, 5], "reversed": false, "direction": [-1, 1], "placed_word": "FISH"}, {"end": [15, 8], "word": "BEE", "start": [13, 8], "reversed": false, "direction": [1, 0], "placed_word": "BEE"}, {"end": [10, 12], "word": "OSTRICH", "start": [10, 6], "reversed": false, "direction": [0, 1], "placed_word": "OSTRICH"}, {"end": [7, 13], "word": "STARFISH", "start": [0, 6], "reversed": false, "direction": [1, 1], "placed_word": "STARFISH"}, {"end": [21, 4], "word": "TAPIR", "start": [21, 0], "reversed": false, "direction": [0, 1], "placed_word": "TAPIR"}]}	\N	\N
9	\N	p_68adde61db37a383	automotive	hard	2025-08-26 16:18:41	\N	0	0	0	[]	20	active	2025-08-26 09:18:41.959174	2025-08-26 09:18:41.959174	20	{"id": "p_68adde61db37a383", "grid": [["S", "U", "Q", "C", "O", "N", "V", "E", "R", "T", "I", "B", "L", "E", " ", "T", "O", "P", "F", "F"], ["H", "T", "I", "G", "O", "W", "L", "S", "J", "G", "T", "O", "B", "W", "S", "N", "L", "T", "D", "S"], ["F", "I", "E", "K", "Y", "M", "C", "U", "P", "O", "T", "G", "J", "O", "F", "I", "A", "T", "N", "M"], ["T", "U", "P", "R", "N", "X", "V", "U", "M", "T", "L", "X", "X", "V", "A", "E", "M", "Z", "D", "G"], ["I", "L", "S", "W", "E", "X", "Z", "S", "J", "O", "M", "O", "O", "N", "R", "O", "O", "F", "M", "D"], ["R", "N", "R", "L", "T", "O", "P", "S", "E", "L", "F", " ", "D", "R", "I", "V", "I", "N", "G", "H"], ["E", "U", "V", "U", "L", "T", "I", "R", "E", " ", "A", "L", "I", "G", "N", "M", "E", "N", "T", "H"], [" ", "J", "C", "I", "N", "N", "P", "E", "E", "L", "I", "P", "J", "K", "D", "L", "Q", "E", "N", "E"], ["B", "U", "I", "C", "K", " ", "M", "L", "I", "J", "A", "G", "U", "A", "R", "E", "N", "G", "B", "A"], ["A", "K", "L", "L", "R", "E", "F", "S", "Y", "Y", "J", "V", "K", "I", "B", "U", "Z", "R", "P", "T"], ["L", "Y", "M", "B", "X", "O", "J", "L", "J", "M", "D", "S", "W", "R", "T", "T", "T", "E", "D", "E"], ["A", "I", "R", "M", "D", "V", "P", "F", "A", "D", "O", "J", "E", "S", "I", "Q", "R", "D", "T", "D"], ["N", "P", "N", "R", "O", "X", "E", "H", "H", "T", "I", "U", "E", "Q", "O", "V", "D", " ", "U", " "], ["C", "J", "M", "F", "Y", "R", "F", "E", "L", "Y", " ", "B", "T", "G", "F", "Q", "Q", "F", "X", "S"], ["E", "C", "K", "F", "I", "L", "G", "P", "J", "E", "U", "T", "K", "H", "A", "Q", "A", "L", "M", "E"], ["G", "O", "U", "X", "C", "N", "I", "A", "T", "B", "B", "N", "I", "W", "I", "C", "S", "A", "P", "A"], ["L", "O", "H", "O", "S", "C", "I", "F", "N", "F", "A", "E", "D", "R", "U", "V", "W", "G", "X", "T"], ["Q", "P", "W", "Y", "T", "R", "Y", "T", "D", "C", "P", "I", "Q", "A", "E", "E", "O", "V", "L", "N"], ["G", "E", "C", "L", "K", "V", "N", "U", "I", "X", "K", "M", "C", "J", "I", "S", "F", "Z", "E", "I"], ["L", "R", "D", "R", "P", "H", "L", "S", "M", "U", "K", "S", "I", "P", "E", "S", "B", "M", "H", "P"]], "seed": null, "size": 20, "words": ["MORGAN", "PLYMOUTH", "INFINITI", "CONVERTIBLE TOP", "TIRE ALIGNMENT", "HYUNDAI", "RED FLAG", "RUN FLAT TIRE", "BESTUNE", "BUICK", "SELF DRIVING", "HEATED SEAT", "BAIC", "JAGUAR", "STEREO", "SIPES", "FIAT", "TIRE BALANCE", "MOONROOF", "COOPER"], "options": {"size": 20, "reverse": false, "diagonals": true, "difficulty": "hard", "word_count": 20}, "failed_words": [], "placed_words": [{"end": [16, 8], "word": "MORGAN", "start": [11, 3], "reversed": false, "direction": [1, 1], "placed_word": "MORGAN"}, {"end": [14, 13], "word": "PLYMOUTH", "start": [7, 6], "reversed": false, "direction": [1, 1], "placed_word": "PLYMOUTH"}, {"end": [18, 8], "word": "INFINITI", "start": [11, 1], "reversed": false, "direction": [1, 1], "placed_word": "INFINITI"}, {"end": [0, 17], "word": "CONVERTIBLE TOP", "start": [0, 3], "reversed": false, "direction": [0, 1], "placed_word": "CONVERTIBLE TOP"}, {"end": [6, 18], "word": "TIRE ALIGNMENT", "start": [6, 5], "reversed": false, "direction": [0, 1], "placed_word": "TIRE ALIGNMENT"}, {"end": [18, 14], "word": "HYUNDAI", "start": [12, 8], "reversed": false, "direction": [1, 1], "placed_word": "HYUNDAI"}, {"end": [16, 17], "word": "RED FLAG", "start": [9, 17], "reversed": false, "direction": [1, 0], "placed_word": "RED FLAG"}, {"end": [17, 14], "word": "RUN FLAT TIRE", "start": [5, 2], "reversed": false, "direction": [1, 1], "placed_word": "RUN FLAT TIRE"}, {"end": [7, 17], "word": "BESTUNE", "start": [13, 11], "reversed": false, "direction": [-1, 1], "placed_word": "BESTUNE"}, {"end": [8, 4], "word": "BUICK", "start": [8, 0], "reversed": false, "direction": [0, 1], "placed_word": "BUICK"}, {"end": [5, 18], "word": "SELF DRIVING", "start": [5, 7], "reversed": false, "direction": [0, 1], "placed_word": "SELF DRIVING"}, {"end": [16, 19], "word": "HEATED SEAT", "start": [6, 19], "reversed": false, "direction": [1, 0], "placed_word": "HEATED SEAT"}, {"end": [18, 12], "word": "BAIC", "start": [15, 9], "reversed": false, "direction": [1, 1], "placed_word": "BAIC"}, {"end": [8, 14], "word": "JAGUAR", "start": [8, 9], "reversed": false, "direction": [0, 1], "placed_word": "JAGUAR"}, {"end": [5, 5], "word": "STEREO", "start": [0, 0], "reversed": false, "direction": [1, 1], "placed_word": "STEREO"}, {"end": [19, 15], "word": "SIPES", "start": [19, 11], "reversed": false, "direction": [0, 1], "placed_word": "SIPES"}, {"end": [2, 17], "word": "FIAT", "start": [2, 14], "reversed": false, "direction": [0, 1], "placed_word": "FIAT"}, {"end": [14, 0], "word": "TIRE BALANCE", "start": [3, 0], "reversed": false, "direction": [1, 0], "placed_word": "TIRE BALANCE"}, {"end": [4, 17], "word": "MOONROOF", "start": [4, 10], "reversed": false, "direction": [0, 1], "placed_word": "MOONROOF"}, {"end": [19, 1], "word": "COOPER", "start": [14, 1], "reversed": false, "direction": [1, 0], "placed_word": "COOPER"}]}	\N	\N
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: wordsearch_dev_user
--

COPY public.users (id, username, email, first_name, last_name, password, created_at, updated_at, is_active, email_verified, reset_token, reset_expires, default_theme, default_level, isadmin, default_diagonals, default_reverse) FROM stdin;
2	wordsearch	wordsearch2@wem-tech.com	Ernie	Moreau	$2y$10$42vod8tbjs5Jaly/EEqYRe3AoIt.4/FqjJXpMPHF5sJt4ENQj/X4G	2025-08-23 17:45:08	2025-08-23 17:45:08	t	f	\N	\N	animals	medium	f	t	t
3	wordsearch3	wordsearch3@wem-tech.com	Ernie3	Moreau3	$2y$10$M2nefGRIhfUU86lqagxx7OxZElFZ0iRBBNN66Gq2SEj6D9SexCGOS	2025-08-25 16:35:49	2025-08-25 16:35:49	t	f	\N	\N	animals	medium	f	t	t
1	ErnieM	wordsearch@wem-tech.com	Ernie	Moreau	$2y$10$bUL8UMRLRIcIlFw2UccShu.Ic4d0DhsiQPGrsIj9jHOYoG81RacRG	2025-08-22 21:46:47	2025-08-25 16:57:53	t	f	\N	\N	technology	hard	t	t	t
\.


--
-- Name: games_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wordsearch_dev_user
--

SELECT pg_catalog.setval('public.games_id_seq', 9, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wordsearch_dev_user
--

SELECT pg_catalog.setval('public.users_id_seq', 3, true);


--
-- Name: games games_pkey; Type: CONSTRAINT; Schema: public; Owner: wordsearch_dev_user
--

ALTER TABLE ONLY public.games
    ADD CONSTRAINT games_pkey PRIMARY KEY (id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: wordsearch_dev_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: wordsearch_dev_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: wordsearch_dev_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: idx_users_email; Type: INDEX; Schema: public; Owner: wordsearch_dev_user
--

CREATE INDEX idx_users_email ON public.users USING btree (email);


--
-- Name: idx_users_username; Type: INDEX; Schema: public; Owner: wordsearch_dev_user
--

CREATE INDEX idx_users_username ON public.users USING btree (username);


--
-- Name: games games_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: wordsearch_dev_user
--

ALTER TABLE ONLY public.games
    ADD CONSTRAINT games_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

