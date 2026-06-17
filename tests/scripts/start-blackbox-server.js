const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

const root = path.resolve(__dirname, '../..');
const host = process.env.BLACKBOX_HOST || '127.0.0.1';
const port = process.env.BLACKBOX_PORT || '8010';
const envFile = process.env.PSB_ENV_FILE || '.env.blackbox';
const envPath = path.isAbsolute(envFile) ? envFile : path.join(root, envFile);

function parseEnv(filePath) {
  const values = {};
  const content = fs.readFileSync(filePath, 'utf8');

  for (const rawLine of content.split(/\r?\n/)) {
    const line = rawLine.trim();

    if (!line || line.startsWith('#') || !line.includes('=')) {
      continue;
    }

    const index = line.indexOf('=');
    const key = line.slice(0, index).trim();
    let value = line.slice(index + 1).trim();

    if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
      value = value.slice(1, -1);
    }

    values[key] = value;
  }

  return values;
}

if (!fs.existsSync(envPath)) {
  console.error(`File ${envFile} belum ada. Salin .env.blackbox.example menjadi .env.blackbox lalu sesuaikan DB jika perlu.`);
  process.exit(1);
}

const blackboxEnv = parseEnv(envPath);
const phpBin = process.env.PHP_BIN || blackboxEnv.PHP_BIN || 'php';

const child = spawn(phpBin, ['-S', `${host}:${port}`, '-t', 'public'], {
  cwd: root,
  env: {
    ...process.env,
    ...blackboxEnv,
    PSB_ENV_FILE: envFile,
  },
  stdio: 'inherit',
  shell: false,
});

const stop = () => {
  if (!child.killed) {
    child.kill('SIGTERM');
  }
};

process.on('SIGINT', stop);
process.on('SIGTERM', stop);

child.on('exit', (code) => {
  process.exit(code || 0);
});
