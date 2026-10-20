import { readFileSync, readdirSync, statSync, writeFileSync } from 'node:fs'
import { join } from 'node:path'
import { getIcons } from '@iconify/utils'

const SOURCE = 'resources/js'
const OUTPUT = 'resources/js/icons.json'
const PATTERN = /['"`]((?:lucide|simple-icons|material-symbols):[a-z0-9]+(?:-[a-z0-9]+)*)['"`]/g

const files = []
const walk = (dir) => {
    for (const name of readdirSync(dir)) {
        const path = join(dir, name)
        if (statSync(path).isDirectory()) {
            walk(path)
        } else if (/\.(vue|js)$/.test(name)) {
            files.push(path)
        }
    }
}
walk(SOURCE)

const byPrefix = {}
for (const file of files) {
    for (const [, name] of readFileSync(file, 'utf8').matchAll(PATTERN)) {
        const [prefix, icon] = name.split(':')
        ;(byPrefix[prefix] ||= new Set()).add(icon)
    }
}

let total = 0
const collections = Object.entries(byPrefix).map(([prefix, icons]) => {
    const names = [...icons].sort()
    const full = JSON.parse(readFileSync(`node_modules/@iconify-json/${prefix}/icons.json`, 'utf8'))
    const subset = getIcons(full, names)
    const missing = names.filter((name) => !subset?.icons[name] && !subset?.aliases?.[name])
    if (missing.length) {
        console.warn(`Tidak ada di ${prefix}: ${missing.join(', ')}`)
    }
    total += names.length - missing.length
    return subset
})

writeFileSync(OUTPUT, JSON.stringify(collections.filter(Boolean)))
console.log(`${total} ikon ditulis ke ${OUTPUT}`)
