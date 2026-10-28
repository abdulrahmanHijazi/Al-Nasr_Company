/**
 * Procedural 3D models for the storefront background — everything is built from
 * Three.js primitives, so there are no model files to download or license.
 * Every builder returns a group roughly 3 units tall, centred on the origin.
 *
 * Keys match App\Enums\Background3dModel on the PHP side.
 */
import * as THREE from 'three';
import { RoundedBoxGeometry } from 'three/examples/jsm/geometries/RoundedBoxGeometry.js';

export type ModelKey = 'solar-panel' | 'battery' | 'cctv-camera' | 'inverter' | 'control-panel' | 'wind-turbine';

export const MODEL_KEYS: ModelKey[] = ['solar-panel', 'battery', 'cctv-camera', 'inverter', 'control-panel', 'wind-turbine'];

export interface Palette {
    primary: THREE.Color;
    secondary: THREE.Color;
    dark: THREE.Color;
}

export interface BuiltModel {
    group: THREE.Group;
    /** called every frame with elapsed seconds and the frame delta */
    tick?: (time: number, delta: number) => void;
}

// -- helpers ---------------------------------------------------------------------------

function metal(color: THREE.ColorRepresentation, roughness = 0.4, metalness = 0.8): THREE.MeshStandardMaterial {
    return new THREE.MeshStandardMaterial({ color, roughness, metalness });
}

function glow(color: THREE.ColorRepresentation, intensity = 1.6): THREE.MeshStandardMaterial {
    return new THREE.MeshStandardMaterial({ color, emissive: color, emissiveIntensity: intensity, roughness: 0.4, metalness: 0 });
}

function texture(width: number, height: number, draw: (ctx: CanvasRenderingContext2D, w: number, h: number) => void): THREE.CanvasTexture {
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    draw(canvas.getContext('2d')!, width, height);

    const tex = new THREE.CanvasTexture(canvas);
    tex.colorSpace = THREE.SRGBColorSpace;
    tex.anisotropy = 4;

    return tex;
}

function add<T extends THREE.Object3D>(parent: THREE.Object3D, child: T, x = 0, y = 0, z = 0): T {
    child.position.set(x, y, z);
    parent.add(child);

    return child;
}

function mesh(geometry: THREE.BufferGeometry, material: THREE.Material): THREE.Mesh {
    const m = new THREE.Mesh(geometry, material);
    m.castShadow = false;

    return m;
}

// -- solar panel ------------------------------------------------------------------------

function cellTexture(): THREE.CanvasTexture {
    return texture(1024, 640, (ctx, w, h) => {
        ctx.fillStyle = '#0b1220';
        ctx.fillRect(0, 0, w, h);

        const cols = 12;
        const rows = 6;
        const gap = 5;
        const cw = (w - gap * (cols + 1)) / cols;
        const ch = (h - gap * (rows + 1)) / rows;

        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                const x = gap + c * (cw + gap);
                const y = gap + r * (ch + gap);
                const g = ctx.createLinearGradient(x, y, x + cw, y + ch);
                g.addColorStop(0, '#1e40af');
                g.addColorStop(0.55, '#172554');
                g.addColorStop(1, '#0f172a');
                ctx.fillStyle = g;
                ctx.fillRect(x, y, cw, ch);

                ctx.strokeStyle = 'rgba(191,219,254,0.55)';
                ctx.lineWidth = 1.4;
                for (let b = 1; b <= 3; b++) {
                    ctx.beginPath();
                    ctx.moveTo(x + (cw * b) / 4, y);
                    ctx.lineTo(x + (cw * b) / 4, y + ch);
                    ctx.stroke();
                }
                ctx.strokeStyle = 'rgba(148,163,184,0.35)';
                ctx.lineWidth = 0.8;
                for (let f = 1; f < 9; f++) {
                    ctx.beginPath();
                    ctx.moveTo(x, y + (ch * f) / 9);
                    ctx.lineTo(x + cw, y + (ch * f) / 9);
                    ctx.stroke();
                }
            }
        }
    });
}

function solarPanel(): BuiltModel {
    const group = new THREE.Group();
    const silver = metal('#cbd5e1', 0.35, 0.9);

    const panel = new THREE.Group();
    add(panel, mesh(new THREE.BoxGeometry(3.5, 0.14, 2.3), silver));
    const cells = add(
        panel,
        mesh(new THREE.PlaneGeometry(3.3, 2.1), new THREE.MeshStandardMaterial({ map: cellTexture(), metalness: 0.55, roughness: 0.22 })),
        0,
        0.075,
        0,
    );
    cells.rotation.x = -Math.PI / 2;
    panel.rotation.x = 0.6; // back edge raised, face toward the viewer and the sun
    panel.position.y = 0.55;
    group.add(panel);

    // stand
    for (const x of [-1.1, 1.1]) {
        const leg = add(group, mesh(new THREE.CylinderGeometry(0.05, 0.05, 1.9, 12), silver), x, -0.35, -0.45);
        leg.rotation.x = 0.35;
        add(group, mesh(new THREE.BoxGeometry(0.16, 0.08, 1.1), silver), x, -1.3, -0.1);
    }
    add(group, mesh(new THREE.BoxGeometry(3.2, 0.08, 0.1), silver), 0, -1.3, 0.35);

    group.position.y = 0.25;

    return { group };
}

// -- lithium battery ---------------------------------------------------------------------

function batteryDisplay(label: string): THREE.CanvasTexture {
    return texture(512, 160, (ctx, w, h) => {
        ctx.fillStyle = '#031225';
        ctx.fillRect(0, 0, w, h);
        ctx.strokeStyle = '#38bdf8';
        ctx.lineWidth = 4;
        ctx.strokeRect(6, 6, w - 12, h - 12);
        ctx.fillStyle = '#7dd3fc';
        ctx.font = 'bold 54px monospace';
        ctx.fillText(label, 28, 72);
        ctx.fillStyle = '#4ade80';
        ctx.fillRect(28, 98, 330, 26);
        ctx.fillStyle = 'rgba(74,222,128,0.25)';
        ctx.fillRect(358, 98, 126, 26);
    });
}

function battery(palette: Palette): BuiltModel {
    const group = new THREE.Group();
    const body = metal('#1f2937', 0.5, 0.55);
    const plate = metal('#374151', 0.35, 0.8);
    const leds: THREE.Mesh[] = [];

    for (let i = 0; i < 3; i++) {
        const unit = new THREE.Group();
        unit.position.y = (i - 1) * 1.02;

        add(unit, mesh(new RoundedBoxGeometry(3.0, 0.88, 1.6, 4, 0.1), body));
        add(unit, mesh(new THREE.BoxGeometry(2.8, 0.6, 0.03), plate), 0, 0, 0.81);
        add(unit, mesh(new THREE.PlaneGeometry(1.15, 0.36), new THREE.MeshBasicMaterial({ map: batteryDisplay(`48V ${100 + i * 0}Ah`) })), -0.55, 0.02, 0.83);

        for (let l = 0; l < 4; l++) {
            const led = add(unit, mesh(new THREE.BoxGeometry(0.16, 0.34, 0.03), glow(l < 3 ? '#4ade80' : '#fbbf24', 1.8)), 0.45 + l * 0.24, 0.02, 0.83);
            leds.push(led);
        }
        // handles
        for (const x of [-1.35, 1.35]) {
            add(unit, mesh(new THREE.BoxGeometry(0.1, 0.34, 0.1), metal('#9ca3af', 0.3, 0.9)), x, 0, 0.86);
        }
        // terminals
        if (i === 2) {
            add(unit, mesh(new THREE.CylinderGeometry(0.12, 0.12, 0.14, 24), glow('#dc2626', 0.6)), -1.0, 0.5, -0.2);
            add(unit, mesh(new THREE.CylinderGeometry(0.12, 0.12, 0.14, 24), metal('#111827', 0.4, 0.6)), 1.0, 0.5, -0.2);
        }
        group.add(unit);
    }

    // brand stripe
    add(group, mesh(new THREE.BoxGeometry(3.04, 0.04, 1.64), new THREE.MeshStandardMaterial({ color: palette.primary, roughness: 0.4, metalness: 0.2 })), 0, 1.48, 0);

    return {
        group,
        tick: (t) => {
            leds.forEach((led, i) => {
                (led.material as THREE.MeshStandardMaterial).emissiveIntensity = 1.2 + Math.sin(t * 2.4 + i) * 0.8;
            });
        },
    };
}

// -- CCTV camera -------------------------------------------------------------------------

function cctvCamera(palette: Palette): BuiltModel {
    const group = new THREE.Group();
    const white = new THREE.MeshStandardMaterial({ color: '#f1f5f9', roughness: 0.45, metalness: 0.25 });
    const dark = new THREE.MeshStandardMaterial({ color: '#0f172a', roughness: 0.3, metalness: 0.6 });

    const cam = new THREE.Group();

    const body = add(cam, mesh(new THREE.CylinderGeometry(0.46, 0.46, 2.3, 56), white));
    body.rotation.z = Math.PI / 2;

    // sun shield
    const hood = add(cam, mesh(new THREE.CylinderGeometry(0.56, 0.56, 2.5, 56, 1, true, -Math.PI / 2, Math.PI), new THREE.MeshStandardMaterial({ color: '#e2e8f0', roughness: 0.4, metalness: 0.3, side: THREE.DoubleSide })), -0.05, 0.02, 0);
    hood.rotation.z = Math.PI / 2;
    hood.rotation.y = Math.PI;

    // lens block
    const ring = add(cam, mesh(new THREE.CylinderGeometry(0.47, 0.47, 0.12, 56), dark), -1.2, 0, 0);
    ring.rotation.z = Math.PI / 2;
    const glass = add(cam, mesh(new THREE.SphereGeometry(0.31, 40, 28), new THREE.MeshStandardMaterial({ color: palette.primary, emissive: palette.primary, emissiveIntensity: 0.55, roughness: 0.08, metalness: 0.9 })), -1.27, 0, 0);
    glass.scale.x = 0.55;

    // IR LEDs
    const irs: THREE.Mesh[] = [];
    for (let i = 0; i < 10; i++) {
        const a = (i / 10) * Math.PI * 2;
        irs.push(add(cam, mesh(new THREE.SphereGeometry(0.035, 10, 10), glow('#ef4444', 2)), -1.27, Math.cos(a) * 0.4, Math.sin(a) * 0.4));
    }

    // mount
    add(cam, mesh(new THREE.SphereGeometry(0.2, 24, 18), metal('#94a3b8', 0.35, 0.9)), 0.7, -0.55, 0);
    const arm = add(cam, mesh(new THREE.CylinderGeometry(0.085, 0.085, 1.3, 18), metal('#94a3b8', 0.35, 0.9)), 1.35, -0.55, 0);
    arm.rotation.z = Math.PI / 2;
    add(cam, mesh(new RoundedBoxGeometry(0.18, 1.5, 1.0, 3, 0.04), metal('#64748b', 0.45, 0.7)), 2.05, -0.55, 0);

    cam.rotation.y = 0.5;
    cam.position.set(-0.4, 0.2, 0);
    group.add(cam);

    return {
        group,
        tick: (t) => {
            irs.forEach((led, i) => {
                (led.material as THREE.MeshStandardMaterial).emissiveIntensity = 1 + Math.abs(Math.sin(t * 3 + i * 0.6)) * 1.8;
            });
            // a lazy scanning sweep, like a real camera tracking
            cam.rotation.y = 0.5 + Math.sin(t * 0.5) * 0.18;
        },
    };
}

// -- inverter ----------------------------------------------------------------------------

function inverterDisplay(): THREE.CanvasTexture {
    return texture(640, 400, (ctx, w, h) => {
        ctx.fillStyle = '#04111f';
        ctx.fillRect(0, 0, w, h);
        ctx.strokeStyle = 'rgba(56,189,248,0.25)';
        ctx.lineWidth = 1;
        for (let x = 0; x < w; x += 40) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, h);
            ctx.stroke();
        }
        ctx.strokeStyle = '#4ade80';
        ctx.lineWidth = 5;
        ctx.beginPath();
        for (let x = 0; x <= w; x += 4) {
            const y = h * 0.62 + Math.sin((x / w) * Math.PI * 6) * 52;
            x === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        }
        ctx.stroke();
        ctx.fillStyle = '#7dd3fc';
        ctx.font = 'bold 76px monospace';
        ctx.fillText('5.0 kW', 36, 96);
        ctx.font = '36px monospace';
        ctx.fillStyle = '#94a3b8';
        ctx.fillText('230V  50Hz  98.4%', 36, 150);
    });
}

function inverter(): BuiltModel {
    const group = new THREE.Group();
    const shell = metal('#e2e8f0', 0.42, 0.45);
    const leds: THREE.Mesh[] = [];

    add(group, mesh(new RoundedBoxGeometry(2.1, 2.9, 0.95, 5, 0.1), shell));

    // heat-sink fins at the back
    for (let i = 0; i < 9; i++) {
        add(group, mesh(new THREE.BoxGeometry(1.9, 2.5, 0.05), metal('#94a3b8', 0.5, 0.8)), 0, 0, -0.5 - i * 0.07 + 0.2);
    }

    add(group, mesh(new THREE.BoxGeometry(1.75, 1.15, 0.03), new THREE.MeshStandardMaterial({ color: '#020617', roughness: 0.1, metalness: 0.9 })), 0, 0.62, 0.49);
    add(group, mesh(new THREE.PlaneGeometry(1.62, 1.02), new THREE.MeshBasicMaterial({ map: inverterDisplay() })), 0, 0.62, 0.51);

    for (const [i, x] of [-0.55, 0, 0.55].entries()) {
        const b = add(group, mesh(new THREE.CylinderGeometry(0.13, 0.13, 0.07, 28), metal('#cbd5e1', 0.3, 0.9)), x, -0.35, 0.5);
        b.rotation.x = Math.PI / 2;
        leds.push(add(group, mesh(new THREE.SphereGeometry(0.05, 14, 14), glow(['#4ade80', '#fbbf24', '#38bdf8'][i], 2)), x, -0.65, 0.5));
    }

    for (const x of [-0.55, 0, 0.55]) {
        add(group, mesh(new THREE.CylinderGeometry(0.11, 0.11, 0.35, 20), metal('#1f2937', 0.5, 0.4)), x, -1.58, 0.1);
    }

    return {
        group,
        tick: (t) => {
            leds.forEach((led, i) => {
                (led.material as THREE.MeshStandardMaterial).emissiveIntensity = 0.8 + Math.abs(Math.sin(t * 1.6 + i * 1.3)) * 2;
            });
        },
    };
}

// -- industrial control panel -------------------------------------------------------------

function plateTexture(text: string): THREE.CanvasTexture {
    return texture(512, 96, (ctx, w, h) => {
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(0, 0, w, h);
        ctx.fillStyle = '#e2e8f0';
        ctx.font = 'bold 54px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(text, w / 2, 66);
    });
}

function controlPanel(palette: Palette): BuiltModel {
    const group = new THREE.Group();
    const lights: THREE.Mesh[] = [];

    add(group, mesh(new RoundedBoxGeometry(2.4, 3.1, 0.95, 4, 0.07), metal('#94a3b8', 0.5, 0.6)));
    add(group, mesh(new THREE.BoxGeometry(2.15, 2.85, 0.05), metal('#cbd5e1', 0.4, 0.7)), 0, 0, 0.5);

    // HMI screen
    add(group, mesh(new THREE.BoxGeometry(1.5, 0.85, 0.03), new THREE.MeshStandardMaterial({ color: '#020617', roughness: 0.1, metalness: 0.9 })), 0, 0.85, 0.54);
    add(group, mesh(new THREE.PlaneGeometry(1.4, 0.75), new THREE.MeshBasicMaterial({ map: inverterDisplay() })), 0, 0.85, 0.56);

    // pilot lights
    ['#ef4444', '#4ade80', '#fbbf24', '#38bdf8'].forEach((c, i) => {
        const l = add(group, mesh(new THREE.CylinderGeometry(0.1, 0.1, 0.12, 24), glow(c, 1.8)), -0.6 + i * 0.4, 0.15, 0.56);
        l.rotation.x = Math.PI / 2;
        lights.push(l);
    });

    // selector switches
    for (const x of [-0.5, 0.5]) {
        const base = add(group, mesh(new THREE.CylinderGeometry(0.19, 0.19, 0.07, 28), metal('#1f2937', 0.4, 0.5)), x, -0.4, 0.55);
        base.rotation.x = Math.PI / 2;
        const knob = add(group, mesh(new THREE.BoxGeometry(0.1, 0.3, 0.1), metal('#e2e8f0', 0.3, 0.9)), x, -0.4, 0.62);
        knob.rotation.z = x < 0 ? 0.6 : -0.4;
    }

    // emergency stop
    const ring = add(group, mesh(new THREE.TorusGeometry(0.27, 0.05, 12, 36), glow('#fde047', 0.9)), 0, -1.0, 0.56);
    const stop = add(group, mesh(new THREE.CylinderGeometry(0.2, 0.22, 0.2, 32), metal('#dc2626', 0.35, 0.3)), 0, -1.0, 0.62);
    stop.rotation.x = Math.PI / 2;
    ring.rotation.z = 0;

    add(group, mesh(new THREE.PlaneGeometry(1.1, 0.2), new THREE.MeshBasicMaterial({ map: plateTexture('AL-NASR') })), 0, -1.28, 0.54);
    add(group, mesh(new THREE.BoxGeometry(2.3, 0.05, 0.9), new THREE.MeshStandardMaterial({ color: palette.primary, roughness: 0.4, metalness: 0.3 })), 0, 1.56, 0);

    // vents
    for (let i = 0; i < 5; i++) {
        add(group, mesh(new THREE.BoxGeometry(1.2, 0.025, 0.04), metal('#475569', 0.6, 0.5)), 0, 1.3 - i * 0.07, 0.54);
    }

    return {
        group,
        tick: (t) => {
            lights.forEach((l, i) => {
                (l.material as THREE.MeshStandardMaterial).emissiveIntensity = 0.7 + Math.abs(Math.sin(t * 1.4 + i * 1.1)) * 2;
            });
        },
    };
}

// -- wind turbine --------------------------------------------------------------------------

function windTurbine(palette: Palette): BuiltModel {
    const group = new THREE.Group();
    const white = new THREE.MeshStandardMaterial({ color: '#f8fafc', roughness: 0.35, metalness: 0.15 });

    add(group, mesh(new THREE.CylinderGeometry(0.08, 0.17, 3.5, 32), white), 0, -0.15, 0);
    add(group, mesh(new THREE.CylinderGeometry(0.7, 0.8, 0.12, 40), metal('#475569', 0.6, 0.5)), 0, -1.95, 0);

    const head = new THREE.Group();
    head.position.set(0, 1.62, 0);
    add(head, mesh(new RoundedBoxGeometry(0.42, 0.38, 1.0, 4, 0.1), white), 0, 0, 0);
    add(head, mesh(new THREE.SphereGeometry(0.2, 28, 20), new THREE.MeshStandardMaterial({ color: palette.primary, roughness: 0.3, metalness: 0.4 })), 0, 0, 0.58);

    const rotor = new THREE.Group();
    rotor.position.set(0, 0, 0.62);
    const bladeShape = new THREE.Shape();
    bladeShape.moveTo(-0.1, 0.1);
    bladeShape.lineTo(0.12, 0.1);
    bladeShape.lineTo(0.035, 1.75);
    bladeShape.lineTo(-0.02, 1.75);
    bladeShape.closePath();
    const bladeGeo = new THREE.ExtrudeGeometry(bladeShape, { depth: 0.03, bevelEnabled: false });

    for (let i = 0; i < 3; i++) {
        const arm = new THREE.Group();
        arm.rotation.z = (i * Math.PI * 2) / 3;
        const blade = add(arm, mesh(bladeGeo, white));
        blade.rotation.y = 0.12;
        rotor.add(arm);
    }
    head.add(rotor);
    group.add(head);

    // the head turns slowly into the wind
    return {
        group,
        tick: (t, dt) => {
            rotor.rotation.z -= dt * 1.7;
            head.rotation.y = Math.sin(t * 0.35) * 0.18;
        },
    };
}

// -- registry -------------------------------------------------------------------------------

export function buildModel(key: ModelKey, palette: Palette): BuiltModel {
    switch (key) {
        case 'solar-panel':
            return solarPanel();
        case 'battery':
            return battery(palette);
        case 'cctv-camera':
            return cctvCamera(palette);
        case 'inverter':
            return inverter();
        case 'control-panel':
            return controlPanel(palette);
        case 'wind-turbine':
            return windTurbine(palette);
    }
}

/** All the models together, arranged as a small cluster that drifts and turns. */
export function buildMixed(palette: Palette): BuiltModel {
    const group = new THREE.Group();
    const layout: Array<{ key: ModelKey; x: number; y: number; z: number; s: number }> = [
        { key: 'solar-panel', x: -2.3, y: -0.9, z: 0.4, s: 0.62 },
        { key: 'battery', x: 2.4, y: -1.0, z: 0.2, s: 0.55 },
        { key: 'inverter', x: 0.1, y: -0.4, z: -0.9, s: 0.55 },
        { key: 'cctv-camera', x: 0.9, y: 1.7, z: 0.5, s: 0.5 },
        { key: 'wind-turbine', x: -2.4, y: 1.4, z: -1.4, s: 0.5 },
        { key: 'control-panel', x: 3.1, y: 1.3, z: -1.2, s: 0.4 },
    ];

    const parts = layout.map((item, i) => {
        const built = buildModel(item.key, palette);
        built.group.scale.setScalar(item.s);
        built.group.position.set(item.x, item.y, item.z);
        group.add(built.group);

        return { built, item, phase: i * 1.3 };
    });

    return {
        group,
        tick: (t, dt) => {
            for (const { built, item, phase } of parts) {
                built.tick?.(t, dt);
                built.group.position.y = item.y + Math.sin(t * 0.7 + phase) * 0.14;
                built.group.rotation.y += dt * 0.25 * (phase % 2 ? 1 : -1);
            }
        },
    };
}

export function disposeObject(root: THREE.Object3D): void {
    root.traverse((child) => {
        const m = child as THREE.Mesh;
        if (m.geometry) m.geometry.dispose();

        const materials = Array.isArray(m.material) ? m.material : m.material ? [m.material] : [];
        for (const material of materials) {
            for (const value of Object.values(material)) {
                if (value instanceof THREE.Texture) value.dispose();
            }
            material.dispose();
        }
    });
}
