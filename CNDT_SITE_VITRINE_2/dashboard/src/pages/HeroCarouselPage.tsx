import { useCallback, useEffect, useState } from "react";
import { toast } from "sonner";
import { ImagePlus, Loader2, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { api, type Article, type Paginated } from "@/lib/api";

// Carrousel de la page d'accueil (hero) — gestion des articles affichés et
// de leur ordre via un menu déroulant de position.

export function HeroCarouselPage() {
  const [articles, setArticles] = useState<Article[] | null>(null);
  const [busy, setBusy] = useState(false);

  const load = useCallback(async () => {
    try {
      const res = await api<Paginated<Article>>("/articles?all=true&per_page=200");
      setArticles(res.data);
    } catch (e: any) {
      toast.error(e.message);
    }
  }, []);

  useEffect(() => { load(); }, [load]);

  const hero = (articles ?? [])
    .filter((a) => a.in_hero)
    .sort((a, b) => (a.hero_position ?? 99) - (b.hero_position ?? 99));
  const others = (articles ?? []).filter((a) => !a.in_hero);

  /** Déplacer un slide vers la position choisie (1..N) et persister l'ordre */
  const moveTo = async (slug: string, target: number) => {
    const current = hero.findIndex((h) => h.slug === slug);
    if (current === -1) return;
    const next = [...hero];
    const [item] = next.splice(current, 1);
    next.splice(target - 1, 0, item);

    setBusy(true);
    try {
      for (let i = 0; i < next.length; i++) {
        const wanted = i + 1;
        if (next[i].hero_position === wanted) continue;
        await api(`/articles/${encodeURIComponent(next[i].slug)}`, {
          method: "PUT", body: { hero_position: wanted },
        });
      }
      toast.success("Ordre du carrousel mis à jour.");
      await load();
    } catch (e: any) {
      toast.error(e.message);
    } finally {
      setBusy(false);
    }
  };

  const addToHero = async (slug: string) => {
    if (!slug) return;
    setBusy(true);
    try {
      await api(`/articles/${encodeURIComponent(slug)}`, { method: "PUT", body: { in_hero: true } });
      toast.success("Article ajouté au carrousel de l'accueil.");
      await load();
    } catch (e: any) {
      toast.error(e.message);
    } finally {
      setBusy(false);
    }
  };

  const removeFromHero = async (slug: string) => {
    setBusy(true);
    try {
      await api(`/articles/${encodeURIComponent(slug)}`, {
        method: "PUT", body: { in_hero: false, hero_position: null },
      });
      toast.success("Article retiré du carrousel.");
      await load();
    } catch (e: any) {
      toast.error(e.message);
    } finally {
      setBusy(false);
    }
  };

  if (!articles) return <Skeleton className="h-48" />;

  return (
    <div className="space-y-6">
      <p className="text-sm text-muted-foreground">
        Les articles ci-dessous défilent dans le carrousel principal de la page d'accueil.
        Changez l'ordre avec le menu déroulant « Position ».
      </p>

      {/* Ajouter un article au carrousel */}
      <div className="flex flex-wrap items-center gap-3">
        <label className="text-sm font-medium">Ajouter un article :</label>
        <select
          className="min-w-[280px] rounded-md border border-input bg-background px-3 py-2 text-sm"
          value=""
          disabled={busy || !others.length}
          onChange={(e) => addToHero(e.target.value)}
        >
          <option value="">
            {others.length ? "— Choisir un article —" : "Tous les articles sont déjà dans le carrousel"}
          </option>
          {others.map((a) => (
            <option key={a.slug} value={a.slug}>{a.title}</option>
          ))}
        </select>
      </div>

      {/* Liste des slides avec dropdown de position */}
      <Card className="overflow-hidden">
        {!hero.length && (
          <p className="p-6 text-sm text-muted-foreground">
            Aucun article dans le carrousel — ajoutez-en un ci-dessus.
          </p>
        )}
        {hero.map((a, index) => (
          <div key={a.slug} className="flex items-center gap-3 border-b px-4 py-3 last:border-b-0">
            {a.cover_image ? (
              <img src={a.cover_image} alt="" className="h-12 w-16 rounded-md object-cover" />
            ) : (
              <div className="flex h-12 w-16 items-center justify-center rounded-md bg-muted">
                <ImagePlus className="h-4 w-4 text-muted-foreground" />
              </div>
            )}
            <div className="min-w-0 flex-1">
              <div className="truncate text-sm font-medium">{a.title}</div>
              <div className="text-xs text-muted-foreground">
                {a.category ? a.category + " · " : ""}{a.is_published ? "publié" : "non publié"}
              </div>
            </div>

            {/* Dropdown de position */}
            <div className="flex items-center gap-2">
              <label className="text-xs text-muted-foreground">Position</label>
              <select
                className="rounded-md border border-input bg-background px-2 py-1.5 text-sm"
                value={index + 1}
                disabled={busy}
                onChange={(e) => moveTo(a.slug, Number(e.target.value))}
              >
                {hero.map((_, i) => (
                  <option key={i} value={i + 1}>{i + 1}</option>
                ))}
              </select>
            </div>

            <Button
              size="sm" variant="ghost" title="Retirer du carrousel"
              className="text-destructive hover:bg-destructive/10"
              disabled={busy}
              onClick={() => removeFromHero(a.slug)}
            >
              <X className="h-4 w-4" />
            </Button>
          </div>
        ))}
      </Card>

      {busy && (
        <p className="flex items-center gap-2 text-sm text-muted-foreground">
          <Loader2 className="h-4 w-4 animate-spin" /> Mise à jour en cours…
        </p>
      )}
    </div>
  );
}
