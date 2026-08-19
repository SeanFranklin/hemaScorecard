/**
 * Pure expected-standings calculator for pool round-robins (no Playwright).
 *
 * A test defines a MatchScript (what happens in each match) and a
 * RankingRule (how the app is configured to rank), and this module derives
 * the standings the app should display. Accumulation semantics were
 * verified against the app's eventStandings table:
 *  - a deductive afterblow nets off the hitter's points in BOTH
 *    pointsFor and pointsAgainst (hit 3 w/ afterblow 1 -> 2 each side)
 *  - a double hit counts once per fighter and awards no points
 *  - hitsAgainst counts scoring hits received (doubles excluded,
 *    afterblow-reduced hits included)
 */

export type Exchange = {
  /** lastName of the fighter who lands the hit (omit for a double). */
  scorer?: string;
  points?: number;
  /** Afterblow points landed by the opponent (deducted from the hit). */
  afterblow?: number;
  double?: boolean;
};

export type MatchPlan = { exchanges: Exchange[]; winner: string };

/** Keyed by pairKey() of the two fighters' lastNames. */
export type MatchScript = Map<string, MatchPlan>;

export type FighterStats = {
  lastName: string;
  wins: number;
  pointsFor: number;
  pointsAgainst: number;
  doubles: number;
  hitsAgainst: number;
};

export type StandingRow = FighterStats & { score: number };

/** Order-independent key for a pairing — match order/sides are app-decided. */
export const pairKey = (a: string, b: string) => [a, b].sort().join('|');

export function accumulateStats(script: MatchScript): Map<string, FighterStats> {
  const stats = new Map<string, FighterStats>();
  const statsFor = (lastName: string) => {
    let s = stats.get(lastName);
    if (!s) {
      s = { lastName, wins: 0, pointsFor: 0, pointsAgainst: 0, doubles: 0, hitsAgainst: 0 };
      stats.set(lastName, s);
    }
    return s;
  };

  for (const [key, plan] of script) {
    const [a, b] = key.split('|');
    statsFor(a);
    statsFor(b);
    statsFor(plan.winner).wins++;

    for (const ex of plan.exchanges) {
      if (ex.double) {
        statsFor(a).doubles++;
        statsFor(b).doubles++;
        continue;
      }
      const scorer = statsFor(ex.scorer!);
      const receiver = statsFor(ex.scorer === a ? b : a);
      const net = (ex.points ?? 0) - (ex.afterblow ?? 0);
      scorer.pointsFor += net;
      receiver.pointsAgainst += net;
      receiver.hitsAgainst++;
    }
  }
  return stats;
}

export type RankingRule = {
  score: (s: FighterStats) => number;
  /** Sort comparator over scored rows; best fighter first. */
  compare: (a: StandingRow, b: StandingRow) => number;
};

/**
 * systemRankings ID 1: score = 5*wins + PF - PA - doubles*(doubles+1)/2;
 * sort score DESC, wins DESC, doubles ASC, hitsAgainst ASC.
 */
export const FRANKLIN_2014: RankingRule = {
  score: (s) =>
    5 * s.wins + s.pointsFor - s.pointsAgainst - (s.doubles * (s.doubles + 1)) / 2,
  compare: (a, b) =>
    b.score - a.score ||
    b.wins - a.wins ||
    a.doubles - b.doubles ||
    a.hitsAgainst - b.hitsAgainst,
};

/** Standings the app should display for this script, best fighter first. */
export function computeExpectedStandings(script: MatchScript, rule: RankingRule): StandingRow[] {
  return [...accumulateStats(script).values()]
    .map((s) => ({ ...s, score: rule.score(s) }))
    .sort(rule.compare);
}
