<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
class AddBoard extends Component
{
    public $title;
    public $list_type;
    public $board;
    public $isOpen = false;

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:100',
            'list_type' => 'required|string|in:normal,business',
        ]);

        $board = Board::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));


        $this->closeModal();

        session()->flash('success', 'Board created successfully.');

        $this->dispatch('board-added', boardId: $board->id);

        return redirect()->route('boards.show', $board->id);

    }

    public function render()
    {
        return view('livewire.add-board');
    }
}
