import { useState, type KeyboardEvent } from "react";
import { X } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

// Saisie d'une liste de valeurs courtes (centres d'intérêts…) :
// chaque valeur saisie (Entrée ou virgule) devient une puce retirable.
// Représentation reprise du site public (chips de la fiche individuelle).

interface TagsFieldProps {
  label: string;
  value: string[];
  onChange: (value: string[]) => void;
}

export function TagsField({ label, value, onChange }: TagsFieldProps) {
  const [draft, setDraft] = useState("");

  const add = (raw: string) => {
    const items = raw
      .split(",")
      .map((t) => t.trim())
      .filter(Boolean)
      .filter((t) => !value.includes(t));
    if (items.length) onChange([...value, ...items]);
    setDraft("");
  };

  const remove = (item: string) => onChange(value.filter((v) => v !== item));

  const onKeyDown = (e: KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter" || e.key === "," ) {
      e.preventDefault();
      add(draft);
    }
    if (e.key === "Backspace" && !draft && value.length) {
      onChange(value.slice(0, -1));
    }
  };

  return (
    <div className="grid gap-2">
      <Label htmlFor="tags-input">{label}</Label>
      <div className="flex flex-wrap items-center gap-1.5 rounded-md border border-border bg-background px-2 py-1.5 min-h-9">
        {value.map((item) => (
          <span
            key={item}
            className="inline-flex items-center gap-1 rounded-full bg-[#e3eee7] px-2.5 py-0.5 text-sm text-[#0d2e20]"
          >
            {item}
            <button
              type="button"
              aria-label={`Retirer ${item}`}
              className="text-[#667069] hover:text-destructive"
              onClick={() => remove(item)}
            >
              <X className="size-3.5" />
            </button>
          </span>
        ))}
        <Input
          id="tags-input"
          className="h-7 flex-1 min-w-[8rem] border-0 shadow-none focus-visible:ring-0 p-1"
          placeholder="Ajouter puis Entrée…"
          value={draft}
          onChange={(e) => setDraft(e.target.value)}
          onKeyDown={onKeyDown}
          onBlur={() => draft.trim() && add(draft)}
        />
      </div>
    </div>
  );
}
