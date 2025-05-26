'use client';

import { useState } from 'react';
import Image from 'next/image';
import { Menu, X, ShoppingCart, UserRoundPlus } from 'lucide-react';
import logo from '@/app/assets/logo.webp';
import LienHeader from './LienHeader';

export default function Navbar() {
    const [isOpen, setIsOpen] = useState(false);

    // 🧪 Simuler un utilisateur connecté (remplace ça par ta logique réelle)
    const user = { name: 'Raphaël' }; // null si déconnecté

    return (
        <div className='navbar'>
        <header className=" shadow-sm border-b border-yellow-200">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3  grid grid-cols-3 md:grid-cols-9 gap-20 items-center">
                {/* Logo */}
                <div className="grid grid-columns md:col-span-2 col-span-1  items-center">
                    <Image src={logo} alt="Logo" className="rounded" />

                </div>

                <LienHeader val='hidden'/>

                {/* Always-visible icons */}
                <div className="grid  grid-cols-4 items-center justify-end  col-span-2 text-end gird md:grid-cols-3">
                    <div className='col-span-1 flex justify-end md:justify-start'>
                        <ShoppingCart className=" text-green-700  hover:text-red-500 cursor-pointer bg-blue "/>
                    </div>

                    <button className=" text-center hover:text-red-500 text-green-700  col-span-2 flex justify-end md:col-span-2">
                        <UserRoundPlus/>
                        <span className='text-xl'>S’identifier</span>
                    </button>
                    {/* Burger menu */}
                    <a onClick={() => setIsOpen(!isOpen)} className="md:hidden  text-green-700  hover:text-red-500 col-span-1 flex justify-end">
                        {isOpen ? <X size={28} /> : <Menu size={28} />}
                    </a>
                </div>
            </div>

            
        </header>
        {/* Mobile dropdown */}
            {isOpen && (
                <div className='md:hidden menu'><LienHeader/></div>
                
            )}
            </div>
    );
}
