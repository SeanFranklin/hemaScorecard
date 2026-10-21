/**
 * Constants matching the seeded fixture (tests/fixtures/seed.sql).
 * See tests/fixtures/credentials.md for the full reference.
 */
export const TEST_EVENT_ID = 1;
export const TEST_EVENT_NAME = 'Playwright Test Event';
export const TEST_TOURNAMENT_ID = 1;

export const ORGANIZER_PASSWORD = 'organizer-test-pw';
export const STAFF_PASSWORD = 'staff-test-pw';

export const FIGHTERS = [
  { rosterID: 1, firstName: 'Alice', lastName: 'Applegate' },
  { rosterID: 2, firstName: 'Brett', lastName: 'Bowman' },
  { rosterID: 3, firstName: 'Carol', lastName: 'Chandler' },
  { rosterID: 4, firstName: 'Dmitri', lastName: 'Dukas' },
  { rosterID: 5, firstName: 'Erin', lastName: 'Eastwood' },
  { rosterID: 6, firstName: 'Frank', lastName: 'Fischer' },
];

/** Event 1 fighters at schools in different countries (school country in comments). */
export const COUNTRY_FIGHTERS = {
  morgan: { rosterID: 11, firstName: 'Mira', lastName: 'Morgan' }, // US
  nash: { rosterID: 12, firstName: 'Nolan', lastName: 'Nash' }, // US
  ortiz: { rosterID: 13, firstName: 'Olive', lastName: 'Ortiz' }, // CA
  paquet: { rosterID: 14, firstName: 'Pierre', lastName: 'Paquet' }, // CA
  quill: { rosterID: 15, firstName: 'Quinn', lastName: 'Quill' }, // no country
  reyes: { rosterID: 16, firstName: 'Rosa', lastName: 'Reyes' }, // no country
};
