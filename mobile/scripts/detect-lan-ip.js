#!/usr/bin/env node
/**
 * detect-lan-ip.js
 * ----------------
 * Finds this machine's LAN IPv4 address and writes it into mobile/.env so a
 * phone running Expo Go can reach the Laravel API (port 8000) and the Reverb
 * WebSocket (port 8085).
 *
 * Why this exists: "127.0.0.1" inside Expo Go means the phone itself, not this
 * computer, so the API URL must be the machine's LAN address. That address is
 * a DHCP lease and changes between networks, so it is detected on every start.
 *
 * Runs automatically before `expo start` (see package.json), or manually:
 *   npm run ip
 *
 * Flags:
 *   --select    Choose interactively when several adapters look valid.
 *   --manual    Type an IP address by hand (useful for tunnels / public hosts).
 *   --tunnel    Keep the current values but disable auto-detection. Pair with
 *               `expo start --tunnel` when the phone and PC are on different
 *               networks.
 *   --tailscale Point the app at this machine's Tailscale IP so a phone on ANY
 *               network (another Wi-Fi, or mobile data) can still reach the API
 *               and Reverb. Requires Tailscale installed and signed in on both
 *               this machine and the phone. Also sets REACT_NATIVE_PACKAGER_HOSTNAME
 *               (in .env.local) so the Metro QR code advertises the Tailscale address.
 *   --lan       Undo --tailscale / --tunnel and go back to LAN auto-detection.
 */

const { execSync } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');
const readline = require('readline');

const ENV_PATH = path.join(__dirname, '..', '.env');
// Per-machine keys such as REACT_NATIVE_PACKAGER_HOSTNAME are rejected by
// @expo/env when they live in .env; they are only allowed in a `.local` file.
const ENV_LOCAL_PATH = path.join(__dirname, '..', '.env.local');

const API_PORT = process.env.DETECT_API_PORT || '8000';
const REVERB_PORT = process.env.DETECT_REVERB_PORT || '8085';

// Adapters belonging to virtual machines, containers or VPNs. Their addresses
// are not reachable from a phone on the Wi-Fi network, so rank them last.
const VIRTUAL_PATTERN =
  /(virtualbox|vmware|hyper-?v|vethernet|docker|bluetooth|tap|tun|wsl|vpn|zscaler|radmin|hamachi|loopback|veth|tailscale|zerotier)/i;

const ARGS = process.argv.slice(2);
const WANT_SELECT = ARGS.includes('--select');
const WANT_MANUAL = ARGS.includes('--manual');
const WANT_TUNNEL = ARGS.includes('--tunnel');
const WANT_TAILSCALE = ARGS.includes('--tailscale');
const WANT_LAN = ARGS.includes('--lan');
const WANT_HELP = ARGS.includes('--help') || ARGS.includes('-h');

// Tailscale hands out addresses from the 100.64.0.0/10 carrier-grade NAT block.
const TAILSCALE_CGNAT = /^100\.(6[4-9]|[7-9]\d|1[01]\d|12[0-7])\./;

function printHelp() {
  console.log(`
Usage: node scripts/detect-lan-ip.js [flags]

Flags:
  --help      Show this help.
  --select    Pick the adapter interactively when several are available.
  --manual    Type the API / Reverb host IP by hand.
  --tunnel    Disable auto-detection so hand-written tunnel values survive.
  --tailscale Use this machine's Tailscale IP (works on any network / mobile data).
  --lan       Go back to LAN auto-detection after --tailscale or --tunnel.

Environment:
  DETECT_LAN_IP=false       In mobile/.env, skips auto-detection entirely.
  DETECT_API_PORT=8000      API port to embed in EXPO_PUBLIC_API_URL.
  DETECT_REVERB_PORT=8085   Reverb port to embed in EXPO_PUBLIC_REVERB_PORT.
`);
}

function isPrivate(ip) {
  return (
    /^192\.168\./.test(ip) ||
    /^10\./.test(ip) ||
    /^172\.(1[6-9]|2\d|3[01])\./.test(ip)
  );
}

function isTailscaleIp(ip) {
  return TAILSCALE_CGNAT.test(ip);
}

/**
 * Resolve this machine's Tailscale IPv4 address, or null when Tailscale is not
 * installed, not running, or not signed in.
 */
function getTailscaleIp() {
  // 1. Prefer the Tailscale CLI - it is authoritative and knows the address even
  //    when several tunnels / exit nodes are configured.
  try {
    const out = execSync('tailscale ip -4', {
      encoding: 'utf8',
      stdio: ['pipe', 'pipe', 'ignore'],
      timeout: 5000,
    });
    const ip = out.trim().split(/\r?\n/)[0]?.trim();
    if (ip && isTailscaleIp(ip)) {
      return ip;
    }
  } catch {
    // CLI missing or not logged in - fall through to the interface scan.
  }

  // 2. Fall back to scanning adapters for a CGNAT (100.64.0.0/10) address.
  for (const addresses of Object.values(os.networkInterfaces())) {
    for (const address of addresses || []) {
      const isIPv4 = address.family === 'IPv4' || address.family === 4;
      if (isIPv4 && !address.internal && isTailscaleIp(address.address)) {
        return address.address;
      }
    }
  }

  return null;
}

/** Compute network address from an IP and netmask (e.g. 192.168.1.0). */
function networkAddress(ip, netmask) {
  const ipParts = ip.split('.').map(Number);
  const maskParts = netmask.split('.').map(Number);
  return ipParts.map((part, i) => part & maskParts[i]).join('.');
}

/** Best-effort default-gateway detection. Returns the gateway IP or null. */
function getDefaultGateway() {
  try {
    if (process.platform === 'win32') {
      const out = execSync(
        'powershell -NoProfile -Command "Get-NetRoute -DestinationPrefix 0.0.0.0/0 | Sort-Object RouteMetric | Select-Object -First 1 -ExpandProperty NextHop"',
        { encoding: 'utf8', stdio: ['pipe', 'pipe', 'ignore'], timeout: 5000 }
      );
      return out.trim() || null;
    }

    if (process.platform === 'darwin') {
      const out = execSync('netstat -rn | grep default | head -n 1', {
        encoding: 'utf8',
        stdio: ['pipe', 'pipe', 'ignore'],
        timeout: 5000,
      });
      const parts = out.trim().split(/\s+/);
      return parts[parts.length - 1] || null;
    }

    // Linux / most Unix
    const out = execSync('ip route show default | head -n 1', {
      encoding: 'utf8',
      stdio: ['pipe', 'pipe', 'ignore'],
      timeout: 5000,
    });
    const match = out.match(/default via ([\d.]+)/);
    return match ? match[1] : null;
  } catch {
    return null;
  }
}

function gatherCandidates() {
  const gateway = getDefaultGateway();
  const candidates = [];

  for (const [name, addresses] of Object.entries(os.networkInterfaces())) {
    for (const address of addresses || []) {
      const isIPv4 = address.family === 'IPv4' || address.family === 4;
      if (!isIPv4 || address.internal) continue;

      const isVirtual = VIRTUAL_PATTERN.test(name);
      const isPrivateIp = isPrivate(address.address);
      const onGatewaySubnet =
        gateway &&
        address.netmask &&
        networkAddress(address.address, address.netmask) ===
          networkAddress(gateway, address.netmask);

      let score = 0;
      if (isPrivateIp) score += 2;
      if (!isVirtual) score += 1;
      if (onGatewaySubnet) score += 6; // strongest signal: the active internet path

      candidates.push({
        name,
        ip: address.address,
        netmask: address.netmask,
        score,
        isPrivateIp,
        isVirtual,
        onGatewaySubnet,
        gateway,
      });
    }
  }

  candidates.sort((a, b) => b.score - a.score);
  return { candidates, gateway };
}

/** Replaces `KEY=...` in place, appending it when the key is absent. */
function upsert(contents, key, value) {
  const pattern = new RegExp(`^${key}=.*$`, 'm');
  if (pattern.test(contents)) {
    return contents.replace(pattern, `${key}=${value}`);
  }
  const trimmed = contents.replace(/\s*$/, '');
  return `${trimmed}${trimmed ? '\n' : ''}${key}=${value}\n`;
}

/** Removes the whole `KEY=...` line when present. */
function removeKey(contents, key) {
  return contents.replace(new RegExp(`^${key}=.*(?:\\r?\\n)?`, 'm'), '');
}

function readFileSafe(filePath) {
  try {
    return fs.readFileSync(filePath, 'utf8');
  } catch {
    return '';
  }
}

/** Writes `KEY=value` into .env.local, creating the file when needed. */
function upsertLocalKey(key, value) {
  fs.writeFileSync(ENV_LOCAL_PATH, upsert(readFileSafe(ENV_LOCAL_PATH), key, value));
}

/** Removes `KEY` from .env.local, if that file exists. */
function removeLocalKey(key) {
  if (fs.existsSync(ENV_LOCAL_PATH)) {
    fs.writeFileSync(ENV_LOCAL_PATH, removeKey(readFileSafe(ENV_LOCAL_PATH), key));
  }
}

function askQuestion(question) {
  const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
  return new Promise((resolve) => {
    rl.question(question, (answer) => {
      rl.close();
      resolve(answer.trim());
    });
  });
}

async function selectCandidate(candidates) {
  console.log('\n[lan-ip] More than one network adapter looks valid:');
  candidates.forEach((c, i) => {
    const tags = [
      c.onGatewaySubnet ? 'default gateway subnet' : null,
      c.isPrivateIp ? 'private' : 'public',
      c.isVirtual ? 'virtual' : 'physical',
    ]
      .filter(Boolean)
      .join(', ');
    console.log(`  ${i + 1}) ${c.ip.padEnd(15)} (${c.name}) — ${tags}`);
  });

  const answer = await askQuestion('\nPick an adapter by number, or press Enter to use the first one: ');
  const index = answer ? parseInt(answer, 10) - 1 : 0;
  if (Number.isNaN(index) || index < 0 || index >= candidates.length) {
    console.log('[lan-ip] Invalid choice, using the first adapter.');
    return candidates[0];
  }
  return candidates[index];
}

async function manualEntry() {
  const ip = await askQuestion('Enter the LAN / tunnel IP for this machine (e.g. 192.168.1.5): ');
  if (!ip || !/[\d.]{7,15}/.test(ip)) {
    console.error('[lan-ip] Invalid IP address. Aborting.');
    process.exit(1);
  }
  return { ip, name: 'manual', score: 0, onGatewaySubnet: false, isPrivateIp: isPrivate(ip), isVirtual: false, gateway: null };
}

function writeEnv(contents, candidate, { allowFallback = true } = {}) {
  contents = upsert(contents, 'EXPO_PUBLIC_API_URL', `http://${candidate.ip}:${API_PORT}/api`);
  contents = upsert(contents, 'EXPO_PUBLIC_REVERB_HOST', candidate.ip);
  contents = upsert(contents, 'EXPO_PUBLIC_REVERB_PORT', REVERB_PORT);

  // The LAN address dies the moment the phone leaves this Wi-Fi; the tailnet
  // address keeps working anywhere, so it makes a good backup.
  const tailscaleIp = allowFallback ? getTailscaleIp() : null;
  const fallbackIp = tailscaleIp && tailscaleIp !== candidate.ip ? tailscaleIp : null;
  contents = fallbackIp
    ? upsert(contents, 'EXPO_PUBLIC_API_URL_FALLBACK', `http://${fallbackIp}:${API_PORT}/api`)
    : removeKey(contents, 'EXPO_PUBLIC_API_URL_FALLBACK');

  // Drop any leftover Tailscale hostname so Metro advertises the LAN address again.
  contents = removeKey(contents, 'REACT_NATIVE_PACKAGER_HOSTNAME');
  fs.writeFileSync(ENV_PATH, contents);
  removeLocalKey('REACT_NATIVE_PACKAGER_HOSTNAME');

  console.log(`[lan-ip] Selected ${candidate.ip} (${candidate.name})`);
  console.log(`[lan-ip]   API    -> http://${candidate.ip}:${API_PORT}/api`);
  console.log(`[lan-ip]   Reverb -> ws://${candidate.ip}:${REVERB_PORT}`);
  if (fallbackIp) {
    console.log(`[lan-ip]   Fallback -> http://${fallbackIp}:${API_PORT}/api (Tailscale)`);
  }
}

function writeTailscaleEnv(contents, ip, lanIp) {
  // Keep auto-detection off, otherwise `npm start` would rewrite these with the
  // LAN IP on the next run.
  contents = upsert(contents, 'DETECT_LAN_IP', 'false');
  contents = upsert(contents, 'EXPO_PUBLIC_API_URL', `http://${ip}:${API_PORT}/api`);
  contents = upsert(contents, 'EXPO_PUBLIC_REVERB_HOST', ip);
  contents = upsert(contents, 'EXPO_PUBLIC_REVERB_PORT', REVERB_PORT);

  // Safety net: if Tailscale is disconnected but the phone is still on this
  // Wi-Fi, the app falls back to the LAN address instead of failing.
  contents = lanIp && lanIp !== ip
    ? upsert(contents, 'EXPO_PUBLIC_API_URL_FALLBACK', `http://${lanIp}:${API_PORT}/api`)
    : removeKey(contents, 'EXPO_PUBLIC_API_URL_FALLBACK');

  // Metro must advertise the Tailscale address too - otherwise the QR code still
  // points at the LAN IP, which the phone cannot reach off-network. @expo/env
  // rejects this key in .env because it is per-machine, so it belongs in .env.local.
  contents = removeKey(contents, 'REACT_NATIVE_PACKAGER_HOSTNAME');
  fs.writeFileSync(ENV_PATH, contents);
  upsertLocalKey('REACT_NATIVE_PACKAGER_HOSTNAME', ip);

  console.log(`[tailscale] Using ${ip}`);
  console.log(`[tailscale]   API    -> http://${ip}:${API_PORT}/api`);
  console.log(`[tailscale]   Reverb -> ws://${ip}:${REVERB_PORT}`);
  console.log(`[tailscale]   Metro  -> http://${ip}:8081`);
  if (lanIp) {
    console.log(`[tailscale]   Fallback -> http://${lanIp}:${API_PORT}/api (LAN)`);
  }
  console.log('[tailscale] Auto LAN detection is now OFF (DETECT_LAN_IP=false).');
  console.log('[tailscale] Run `npm run start:tailscale`, or `npm run ip -- --lan` to go back.');
}

async function main() {
  if (WANT_HELP) {
    printHelp();
    return;
  }

  let contents = '';
  try {
    contents = fs.readFileSync(ENV_PATH, 'utf8');
  } catch {
    console.warn('[lan-ip] mobile/.env not found - creating it.');
  }

  // Tailscale mode: point every consumer (API, Reverb, Metro) at the tailnet
  // address so the phone works from any network, not just this machine's LAN.
  if (WANT_TAILSCALE) {
    const ip = getTailscaleIp();
    if (!ip) {
      console.error('[tailscale] No Tailscale IPv4 address found.');
      console.error('[tailscale] Install and sign in to Tailscale on this machine, and add this');
      console.error('[tailscale] phone to the same tailnet, then re-run: npm run ip -- --tailscale');
      process.exit(1);
    }
    // Keep the best LAN address as a backup for when the phone is at home.
    const { candidates } = gatherCandidates();
    const lan = candidates.find((c) => c.isPrivateIp && !c.isVirtual) ?? null;
    writeTailscaleEnv(contents, ip, lan?.ip ?? null);
    return;
  }

  // Escape hatch: set DETECT_LAN_IP=false when the app is pointed somewhere
  // other than this machine's LAN address (a tunnel, staging or production),
  // so the hand-written values survive `npm start`. `--lan` overrides it.
  if (!WANT_LAN && /^\s*DETECT_LAN_IP\s*=\s*(false|0|no)\s*$/im.test(contents)) {
    console.log('[lan-ip] DETECT_LAN_IP=false - leaving mobile/.env untouched.');
    return;
  }

  if (WANT_LAN) {
    // Coming back from Tailscale / tunnel mode: re-arm auto-detection and stop
    // Metro from advertising the old hostname.
    contents = upsert(contents, 'DETECT_LAN_IP', 'true');
    contents = removeKey(contents, 'REACT_NATIVE_PACKAGER_HOSTNAME');
    fs.writeFileSync(ENV_PATH, contents);
    removeLocalKey('REACT_NATIVE_PACKAGER_HOSTNAME');
    console.log('[lan-ip] LAN auto-detection re-enabled.');
  }

  if (WANT_TUNNEL) {
    contents = upsert(contents, 'DETECT_LAN_IP', 'false');
    fs.writeFileSync(ENV_PATH, contents);
    console.log('[lan-ip] Tunnel mode enabled. Auto-detection is now OFF.');
    console.log('[lan-ip] Set EXPO_PUBLIC_API_URL and EXPO_PUBLIC_REVERB_HOST to your tunnel/public endpoint,');
    console.log('[lan-ip] then run `npm run start:tunnel`.');
    return;
  }

  if (WANT_MANUAL) {
    const candidate = await manualEntry();
    // A hand-entered host is deliberate - do not second-guess it with a fallback.
    writeEnv(contents, candidate, { allowFallback: false });
    return;
  }

  const { candidates, gateway } = gatherCandidates();

  if (!candidates.length) {
    console.warn('[lan-ip] No LAN IPv4 address found - is Wi-Fi / Ethernet connected?');
    console.warn('[lan-ip] Leaving mobile/.env untouched.');
    return;
  }

  const best = candidates[0];
  const runnersUp = candidates.slice(1);

  // If the best candidate is not on the gateway subnet, warn: we may have picked
  // a secondary adapter. Let the user override if they want.
  if (gateway && !best.onGatewaySubnet) {
    console.warn(`[lan-ip] Default gateway is ${gateway}, but the best-scoring adapter (${best.name}) is on a different subnet.`);
    console.warn('[lan-ip] This can happen with VPNs, virtual switches, or bridged networks.');
    if (WANT_SELECT) {
      const chosen = await selectCandidate(candidates);
      writeEnv(contents, chosen);
      return;
    }
    console.warn('[lan-ip] Using best guess. Re-run with `npm run ip -- --select` to pick manually.');
  } else if (WANT_SELECT && runnersUps.length) {
    const chosen = await selectCandidate(candidates);
    writeEnv(contents, chosen);
    return;
  }

  writeEnv(contents, best);
}

main().catch((err) => {
  console.error('[lan-ip] Unexpected error:', err);
  process.exit(1);
});
