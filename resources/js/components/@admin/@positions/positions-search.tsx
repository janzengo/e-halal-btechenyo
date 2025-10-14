import { Input } from '@/components/ui/input';
import { Search } from 'lucide-react';

interface PositionsSearchProps {
    searchTerm: string;
    onSearchChange: (value: string) => void;
    placeholder?: string;
}

export function PositionsSearch({ 
    searchTerm, 
    onSearchChange, 
    placeholder = "Search positions..." 
}: PositionsSearchProps) {
    return (
        <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
            <Input
                type="text"
                placeholder={placeholder}
                value={searchTerm}
                onChange={(e) => onSearchChange(e.target.value)}
                className="pl-10 focus:border-green-500 focus:ring-green-500 h-11"
            />
        </div>
    );
}
