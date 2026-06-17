const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

const root = path.resolve(__dirname, '../..');
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

function mysqlArgs(env) {
  const args = [
    '--protocol=tcp',
    '-h',
    env.DB_HOST || '127.0.0.1',
    '-P',
    env.DB_PORT || '3306',
    '-u',
    env.DB_USERNAME || 'root',
  ];

  if (env.DB_PASSWORD) {
    args.push(`--password=${env.DB_PASSWORD}`);
  }

  return args;
}

function runMysql(env, sql, label) {
  const mysqlBin = process.env.MYSQL_BIN || env.MYSQL_BIN || 'mysql';
  const result = spawnSync(mysqlBin, mysqlArgs(env), {
    cwd: root,
    input: sql,
    encoding: 'utf8',
    stdio: ['pipe', 'pipe', 'pipe'],
  });

  if (result.status !== 0) {
    console.error(`Gagal menjalankan MySQL untuk ${label}.`);
    console.error(result.stderr || result.stdout);
    process.exit(result.status || 1);
  }
}

function cleanDirectoryContents(dirPath) {
  if (!fs.existsSync(dirPath)) {
    fs.mkdirSync(dirPath, { recursive: true });
    return;
  }

  for (const entry of fs.readdirSync(dirPath)) {
    if (entry === '.gitkeep') {
      continue;
    }

    fs.rmSync(path.join(dirPath, entry), { recursive: true, force: true });
  }
}

if (!fs.existsSync(envPath)) {
  console.error(`File ${envFile} belum ada. Salin .env.blackbox.example menjadi .env.blackbox lalu jalankan ulang.`);
  process.exit(1);
}

const env = parseEnv(envPath);
const database = env.DB_DATABASE;

if (!database || !/(?:_blackbox|_test)$/i.test(database)) {
  console.error('Reset DB dibatalkan. DB_DATABASE harus berakhiran _blackbox atau _test.');
  process.exit(1);
}

const sqlPath = path.join(root, 'database/pendaftaran_siswa.sql');
let seedSql = fs.readFileSync(sqlPath, 'utf8');

seedSql = seedSql.replace(
  /CREATE DATABASE IF NOT EXISTS\s+pendaftaran_siswa[\s\S]*?;\s*USE\s+pendaftaran_siswa\s*;/i,
  `USE \`${database}\`;`
);

runMysql(
  env,
  `DROP DATABASE IF EXISTS \`${database}\`;\nCREATE DATABASE \`${database}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n`,
  'membuat ulang database test'
);
runMysql(env, seedSql, 'import seed database test');

cleanDirectoryContents(path.join(root, 'storage/uploads'));
cleanDirectoryContents(path.join(root, 'storage/sessions'));

console.log(`Database blackbox ${database} berhasil direset.`);
