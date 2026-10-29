import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { createInertiaApp } from '@inertiajs/react'
import { Toaster } from '@/components/ui/sonner'
import './index.css'

// Dashboard CNDT — application Inertia : chaque section (Actualités,
// Publications, Galerie, Personnel, Messages…) a sa propre page Laravel
// (/admin/articles, /admin/publications, …) rendue côté client.
//
// NB : import dynamique via import.meta.glob (rolldown/Vite 8 ne gère pas
// les imports dynamiques à variable multi-niveaux).
const pages = import.meta.glob('./pages/Dashboard/*.tsx') as unknown as
  Record<string, () => Promise<{ default: React.ComponentType<unknown> }>>

createInertiaApp({
  title: (title) => `${title || 'Tableau de bord'} | CNDT`,
  resolve: ((name: string) => {
    // Recherche par suffixe : rolldown peut omettre le préfixe "./" des clés
    const charge = Object.entries(pages)
      .find(([chemin]) => chemin.endsWith(`/pages/${name}.tsx`))?.[1]
    if (!charge) throw new Error(`Page Inertia inconnue : ${name}`)
    return charge()
  }) as unknown as never,
  setup({ el, App, props }) {
    if (!el) return
    createRoot(el).render(
      <StrictMode>
        <App {...props} />
        <Toaster richColors position="bottom-center" />
      </StrictMode>,
    )
  },
})
