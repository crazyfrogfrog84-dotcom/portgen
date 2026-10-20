import fs from 'node:fs';
import path from 'node:path';
import { chromium } from '@playwright/test';

const destination = path.resolve('database/seeders/fixtures');
fs.mkdirSync(destination, { recursive: true });

const portrait = `<svg xmlns="http://www.w3.org/2000/svg" width="480" height="640" viewBox="0 0 480 640">
  <defs>
    <linearGradient id="room" x2="0" y2="1"><stop stop-color="#c4dbc9"/><stop offset="1" stop-color="#edf2e7"/></linearGradient>
    <linearGradient id="shirt" x2="1" y2="1"><stop stop-color="#315e48"/><stop offset="1" stop-color="#183d32"/></linearGradient>
    <pattern id="grain" width="7" height="7" patternUnits="userSpaceOnUse"><circle cx="1" cy="2" r=".45" fill="#17281d" opacity=".12"/></pattern>
  </defs>
  <rect width="480" height="640" fill="url(#room)"/>
  <path d="M0 490 105 365h64L35 640H0zm480-115L357 640h-65l152-265z" fill="#9cbba4" opacity=".42"/>
  <rect width="480" height="640" fill="url(#grain)"/>
  <path d="M64 640c14-131 66-188 175-188s161 57 177 188" fill="url(#shirt)"/>
  <path d="M190 416h100v96c-21 28-77 28-100 0z" fill="#bb7958"/>
  <ellipse cx="240" cy="282" rx="111" ry="145" fill="#d39a74"/>
  <path d="M128 285c-9-114 35-190 117-190 77 0 124 60 113 167-19-25-30-50-36-84-46 40-113 55-191 44z" fill="#26352d"/>
  <path d="M137 233c-16-56-1-124 31-155-7 56-2 90-31 155zm199-17c32-30 42-87 31-129 42 49 40 106 18 151z" fill="#26352d"/>
  <ellipse cx="201" cy="293" rx="7" ry="5" fill="#29332d"/><ellipse cx="279" cy="293" rx="7" ry="5" fill="#29332d"/>
  <path d="M228 321q12 8 24 0" fill="none" stroke="#9c5c4a" stroke-width="5" stroke-linecap="round"/>
  <path d="M205 365q35 24 70 0" fill="none" stroke="#77483f" stroke-width="5" stroke-linecap="round"/>
  <path d="M95 640c17-85 66-135 143-144l-48 144zm290 0c-17-85-66-135-143-144l48 144z" fill="#e5ece0" opacity=".28"/>
</svg>`;

const palettes = [
    ['#cce2d1', '#315c49', '#f0d4a1'], ['#d4e3d4', '#4b675d', '#adc7b3'],
    ['#d7e0d2', '#77634b', '#c5d4be'], ['#d5e3dc', '#315e62', '#e5c895'],
    ['#d9e4d1', '#4f6242', '#c6b88d'], ['#d4e0d8', '#36546b', '#b2d0bd'],
];

function cover(index) {
    const [background, foreground, detail] = palettes[index];
    const seed = 34 + index * 13;
    return `<svg xmlns="http://www.w3.org/2000/svg" width="960" height="540" viewBox="0 0 960 540">
      <defs><pattern id="grid" width="36" height="36" patternUnits="userSpaceOnUse"><path d="M36 0H0v36" fill="none" stroke="${foreground}" stroke-opacity=".13"/></pattern><linearGradient id="fade" x2="1" y2="1"><stop stop-color="${background}"/><stop offset="1" stop-color="#f4f3e9"/></linearGradient></defs>
      <rect width="960" height="540" fill="url(#fade)"/><rect width="960" height="540" fill="url(#grid)"/>
      <circle cx="${190 + seed}" cy="${160 + seed}" r="180" fill="${foreground}" opacity=".22"/>
      <path d="M${300 + seed} 540 610 20h170L510 540z" fill="${detail}" opacity=".75"/>
      <circle cx="760" cy="390" r="122" fill="none" stroke="${foreground}" stroke-width="5" opacity=".45"/>
      <path d="M82 414h286M82 442h190" stroke="${foreground}" stroke-width="5" stroke-linecap="round" opacity=".56"/>
      <rect x="72" y="76" width="28" height="28" rx="14" fill="${foreground}" opacity=".72"/>
    </svg>`;
}

const browser = await chromium.launch({ headless: true });
try {
    const page = await browser.newPage({ viewport: { width: 960, height: 640 } });
    const render = async (svg, file, width, height) => {
        await page.setContent(`<html><body style="margin:0"><div>${svg}</div></body></html>`);
        await page.locator('svg').screenshot({ path: path.join(destination, file), scale: 'css' });
        const size = await page.locator('svg').evaluate((element) => ({ width: element.width.baseVal.value, height: element.height.baseVal.value }));
        if (size.width !== width || size.height !== height) throw new Error('Fixture SVG dimensions changed unexpectedly.');
    };
    await render(portrait, 'simple-portrait.png', 480, 640);
    for (let index = 0; index < palettes.length; index++) {
        await render(cover(index), `simple-cover-${index + 1}.png`, 960, 540);
    }
} finally {
    await browser.close();
}
